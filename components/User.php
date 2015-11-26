<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 25.03.2015
 * Time: 13:56 ч.
 */

namespace nkostadinov\user\components;

use nkostadinov\user\behaviors\LastLoginBehavior;
use nkostadinov\user\events\UserRegisterEvent;
use nkostadinov\user\interfaces\IUserNotificator;
use nkostadinov\user\models\Token;
use nkostadinov\user\models\User as UserModel;
use nkostadinov\user\models\UserSearch;
use nkostadinov\user\models\UserAccount;
use yii\authclient\ClientInterface;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\web\User as BaseUser;

class User extends BaseUser
{
    const LOG_CATEGORY = 'app.user';
    //event constants
    const EVENT_BEFORE_REGISTER = 'user.before.register';
    const EVENT_AFTER_REGISTER = 'user.after.register';

    public $loginForm = 'nkostadinov\user\models\forms\LoginForm';
    public $registerForm = 'nkostadinov\user\models\forms\SignupForm';
    public $recoveryForm = 'nkostadinov\user\models\forms\RecoveryForm';

    public $enableConfirmation = true;
    public $allowUncofirmedLogin = false;
    public $requireUsername = false;

    public $identityClass = 'nkostadinov\user\models\User';
    public $enableAutoLogin = true;
    public $loginUrl = ['user/security/login'];

    /**
     * The minimum length that a password field can have.
     * @var integer
     */
    public $minPasswordLength = 6;

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

    public function findUserByEmail($email)
    {
        return UserModel::findOne([
            'email' => $email,
        ]);
    }

    /**
     * @return IUserNotificator
     */
    public function getNotificator()
    {
        if(!isset($this->_notificator)) {
            $this->_notificator = \Yii::createObject($this->components['notificator']);
            Instance::ensure($this->_notificator, 'nkostadinov\user\interfaces\IUserNotificator');
        }
        return $this->_notificator;
    }

    public function addAccount(ClientInterface $client)
    {

    }

    /**
     * Performs the actual user registration by validation the data and persisting the user.
     *
     * @param UserModel $model
     * @return bool
     */
    public function register(\nkostadinov\user\models\User $model)
    {
        if ($this->enableConfirmation == false) {
            $model->confirmed_on = time();
        }
//        if ($this->module->enableGeneratingPassword) {
//            $this->password = Password::generate(8);
//        }
        $model->register_ip = \Yii::$app->getRequest()->isConsoleRequest ? '(console)' : \Yii::$app->getRequest()->getUserIP();

        $event = new UserRegisterEvent();
        $event->model = $model;
        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);

        if ($model->save()) {
            //Raise event that the user is persisted
            $this->trigger(self::EVENT_AFTER_REGISTER, $event);
            //Add confirmation token(if enabled) and notify user
            if ($this->enableConfirmation) {
                $token = \Yii::createObject([
                    'class' => Token::className(),
                    'type' => Token::TYPE_CONFIRMATION,
                ]);
                $token->link('user', $model);
                $this->getNotificator()->sendConfirmationMessage($model, $token);
            } else {
                $this->login($model);
            }
//            if ($this->module->enableGeneratingPassword) {
//                $this->mailer->sendWelcomeMessage($this);
//            }
            return true;
        }
        \Yii::error('An error occurred while registering user account', self::LOG_CATEGORY . '.register');
        return false;
    }

    public function findAccount(ClientInterface $client)
    {
        return UserAccount::findOne([
            'provider' => $client->name,
            'client_id' => $client->getUserAttributes()['id']
        ]);
    }

    public function getName()
    {
        if($this->isGuest)
            return 'Guest';
        return $this->identity->getDisplayName();
    }

}