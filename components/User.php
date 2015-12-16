<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 25.03.2015
 * Time: 13:56 ч.
 */

namespace nkostadinov\user\components;

use nkostadinov\user\behaviors\LastLoginBehavior;
use nkostadinov\user\helpers\Event;
use nkostadinov\user\interfaces\IUserNotificator;
use nkostadinov\user\models\Token;
use nkostadinov\user\models\User as UserModel;
use nkostadinov\user\models\UserSearch;
use nkostadinov\user\validators\PasswordStrengthValidator;
use Yii;
use yii\di\Instance;
use yii\web\User as BaseUser;

class User extends BaseUser
{
    /** Event triggered before registration. Triggered with UserEvent. */
    const EVENT_BEFORE_REGISTER = 'nkostadinov.user.beforeRegister';
    /** Event triggered after registration. Triggered with UserEvent. */
    const EVENT_AFTER_REGISTER = 'nkostadinov.user.afterRegister';

    public $loginForm = 'nkostadinov\user\models\forms\LoginForm';
    public $registerForm = 'nkostadinov\user\models\forms\SignupForm';
    public $recoveryForm = 'nkostadinov\user\models\forms\RecoveryForm';        
    /** @var string The class name of the form used for changing passwords. */
    public $changePasswordForm = 'nkostadinov\user\models\forms\ChangePasswordForm';
    /** @var string The class name of the form used for acquiring the user's email when it cannot be fetched via a social network. */
    public $acquireEmailForm = 'nkostadinov\user\models\forms\AcquireEmailForm';

    public $enableConfirmation = true;
    public $allowUncofirmedLogin = false;
    public $requireUsername = false;

    public $identityClass = 'nkostadinov\user\models\User';
    public $enableAutoLogin = true;
    public $loginUrl = ['user/security/login'];
    /** @var integer The minimum length that a password field can have. */
    public $minPasswordLength = 6;
    /** @var integer The time for which the use is locked. Defaults to 1 hour (in seconds). */
    public $lockExpiration = 3600;
    /** @var array Configurations for the password strength validator. Defaults to '['preset' => PasswordStrengthValidator::NORMAL]' */
    public $passwordStrengthConfig = ['preset' => PasswordStrengthValidator::NORMAL];
    /** @var array The access rules of the admin panel */
    public $adminRules = [
        [
            'allow' => true,
            'roles' => ['@']
        ]
    ];

    public $components = [
        'notificator' => 'nkostadinov\user\components\MailNotificator',
    ];

    private $_notificator;

    public function behaviors()
    {
        return [
            'last_login' => LastLoginBehavior::className()
        ];
    }
    
    public function listUsers($params)
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($params);
        return $dataProvider;
    }

    /**
     * @return IUserNotificator
     */
    public function getNotificator()
    {
        if(!isset($this->_notificator)) {
            $this->_notificator = Yii::createObject($this->components['notificator']);
            Instance::ensure($this->_notificator, 'nkostadinov\user\interfaces\IUserNotificator');
        }
        return $this->_notificator;
    }

    /**
     * Performs the actual user registration by validation the data and persisting the user.
     *
     * @param UserModel $model
     * @return bool
     */
    public function register(UserModel $model)
    {
        Yii::info("Registering user [$model->email]", __CLASS__);
        if ($this->enableConfirmation == false) {
            $model->confirmed_on = time();
        }

        $event = Event::createUserEvent($model);
        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);

        Yii::info("Saving user [$model->email] to the database", __CLASS__);
        if ($model->save()) {
            Yii::info("User [$model->email] successfuly registered!", __CLASS__);
            // Raise event that the user is persisted
            $this->trigger(self::EVENT_AFTER_REGISTER, $event);
            //Add confirmation token(if enabled) and notify user
            if ($this->enableConfirmation) {
                Yii::info("Creating token of user [$model->email]", __CLASS__);
                $token = Yii::createObject([
                    'class' => Token::className(),
                    'type' => Token::TYPE_CONFIRMATION,
                ]);
                $token->link('user', $model);
                
                Yii::info("Sending confirmation email to [$model->email]", __CLASS__);
                $this->getNotificator()->sendConfirmationMessage($model, $token);
            }
            
            return true;
        }
        
        Yii::error("An error occurred while registering user [$model->email][$model->register_ip]", __CLASS__);
        return false;
    }

    public function getName()
    {
        if($this->isGuest)
            return 'Guest';
        return $this->identity->getDisplayName();
    }

}
