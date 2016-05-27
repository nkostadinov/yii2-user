<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 25.03.2015
 * Time: 13:56 ч.
 */

namespace nkostadinov\user\components;

use nkostadinov\user\behaviors\LastLoginBehavior;
use nkostadinov\user\exceptions\DuplicatedUserException;
use nkostadinov\user\exceptions\MissingEmailException;
use nkostadinov\user\helpers\Event;
use nkostadinov\user\interfaces\IUserAccount;
use nkostadinov\user\interfaces\IUserNotificator;
use nkostadinov\user\models\Token;
use nkostadinov\user\models\User as UserModel;
use nkostadinov\user\models\UserAccount;
use nkostadinov\user\validators\PasswordStrengthValidator;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\NotSupportedException;
use yii\di\Instance;
use yii\gii\Module;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\User as BaseUser;

class User extends BaseUser
{    
    /** Event triggered before registration. Triggered with UserEvent. */
    const EVENT_BEFORE_REGISTER = 'nkostadinov.user.beforeRegister';
    /** Event triggered after registration. Triggered with UserEvent. */
    const EVENT_AFTER_REGISTER = 'nkostadinov.user.afterRegister';
    /** Event is triggered before authenticating the user by an OAuth2 authentication. Triggered with \nkostadinov\user\events\AuthEvent. */
    const EVENT_BEFORE_OAUTH = 'nkostadinov.user.beforeOAuth';
    /** Event is triggered after authenticating the user by an OAuth2 authentication. Triggered with \nkostadinov\user\events\AuthEvent. */
    const EVENT_AFTER_OAUTH = 'nkostadinov.user.afterOAuth';

    const CLIENT_PARAM = 'oAuthClient';

    public $loginForm = 'nkostadinov\user\models\forms\LoginForm';
    public $registerForm = 'nkostadinov\user\models\forms\SignupForm';
    public $recoveryForm = 'nkostadinov\user\models\forms\RecoveryForm';        
    /** @var string The class name of the form used for changing passwords. */
    public $changePasswordForm = 'nkostadinov\user\models\forms\ChangePasswordForm';
    /** @var string The class name of the form used for acquiring the user's email when it cannot be fetched via a social network. */
    public $acquireEmailForm = 'nkostadinov\user\models\forms\AcquireEmailForm';
    /** @var string The class name of the form used for reseting the user's password. */
    public $resetPasswordForm = 'nkostadinov\user\models\forms\ResetPasswordForm';

    public $enableConfirmation = true;
    public $allowUncofirmedLogin = false;
    public $requireUsername = false;

    public $identityClass = 'nkostadinov\user\models\User';
    public $tokenClass = 'nkostadinov\user\models\Token';
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
    
    public function listUsers($params = [])
    {
        return call_user_func([ $this->identityClass, 'find'])
            ->andFilterWhere($params);
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
        
        Yii::error("An error occurred while registering user [$model->email][$model->register_ip].\n" .
            VarDumper::dumpAsString($model->getErrors()), __CLASS__);
        return false;
    }

    /**
     *
     * @param \nkostadinov\user\components\ClientInterface $client
     * @return type
     * @throws NotSupportedException
     */
    public function oAuthAuthentication(ClientInterface $client)
    {
        if(!$client instanceof IUserAccount) {
            throw new NotSupportedException('Your client must extend the IUserInterface.');
        }

        $account = UserAccount::findByClient($client);
        if(empty($account)) { // If account doesn't exist, create it
            Yii::info("Creating user account for user [$client->id][$client->userId]", __CLASS__);
            $account = UserAccount::createAndSave($client);
        }

        $event = Event::createAuthEvent($account, $client);
        $this->trigger(self::EVENT_BEFORE_OAUTH, $event);

        $result = true;
        if(!$account->user) { // Create a new user or link account to an existing user
            if (Yii::$app->user->isGuest) { // This means the user comes for a first time or has a user created by a regular login or another client
                $email = $client->getEmail();
                if (is_null($email)) { // Sometimes the email cannot be fetched from the client
                    Yii::info("Unable to fetch the email of account [$client->id][$client->userId]", __CLASS__);
                    throw new MissingEmailException();
                } else {
                    try {
                        $result = $this->createUserByOAuthIfNotExists($client, $account, $email);
                    } catch (DuplicatedUserException $exception) {
                        throw $exception;
                    }
                }
            } else { // Link account to user
                // This means the user is logged in through a regular login or another client. Needs to be linked.
                $email = Yii::$app->user->identity->email;
                Yii::info("Linking user [$email] to account [$client->id][$client->userId]", __CLASS__);
                $account->link('user', Yii::$app->user->identity);
            }
        } else if (Yii::$app->user->isGuest) {
            Yii::info("Logging in user [{$account->user->email}]", __CLASS__);
            $result = Yii::$app->user->login($account->user);
        }

        $this->trigger(self::EVENT_AFTER_OAUTH, $event);
        return $result;
    }

    public function getName()
    {
        if($this->isGuest)
            return 'Guest';
        return $this->identity->getDisplayName();
    }

    public function createUserByOAuthIfNotExists(IUserAccount $client, $account, $email)
    {
        Yii::info("Trying to create a new user for account [$client->id][$client->userId][$email]", __CLASS__);

        $user = call_user_func([$this->identityClass, 'findByEmail'], ['email' => $email]);
        if (!$user) {
            Yii::info("Creating a new user for account [$client->id][$client->userId][$email]", __CLASS__);

            $user = new $this->identityClass();
            $user->email = $email;
            $user->name = $client->getRealName();
            $user->save(false);

            Yii::info("User successfuly created for account [$client->id][$client->userId][$email]", __CLASS__);
        } else if ($user->password_hash) {
            throw new DuplicatedUserException();
        }

        Yii::info("Linking user [$email] to account [$client->id][$client->userId]", __CLASS__);
        $account->link('user', $user);

        if (Yii::$app->user->login($user)) {
            Yii::info("Logging in user [$client->id][$client->userId][$email]", __CLASS__);
            return true;
        }

        Yii::error("Unable to login user [$client->id][$client->userId][$email]", __CLASS__);
        return false;
    }

    public function lockUser(UserModel $model)
    {
        Yii::info("Locking user '$model->id'", __CLASS__);
        
        $model->locked_until = time() + $this->lockExpiration;
        
        return $model->save(false);
    }

    public function unlockUser(UserModel $model)
    {
        Yii::info("Unlocking user '$model->id'", __CLASS__);

        $model->login_attempts = 0;
        $model->locked_until = null;
        
        return $model->save(false);
    }

    /**
     * Confirms the registration of the user by the given token.
     *
     * @param Token $token
     * @return boolean True on success, false otherwise.
     * @throws NotFoundHttpException
     */
    public function confirmUser($user, $token)
    {
        Yii::info('User is trying to confirm the registration', __CLASS__);
        if (empty($token) || $token->isExpired) {
            Yii::info('User\'s confirmation code not found or expired', __CLASS__);
            throw new NotFoundHttpException(Yii::t(Module::I18N_CATEGORY, 'Confirmation code not found or expired!'));
        }

        $token->delete();
        $user->confirmed_on = time();

        return $user->save(false);
    }

    public function resetPassword($tokenCode, $newPassword)
    {
        Yii::info("Fetching token", __CLASS__);
        $token = Token::findByCode($tokenCode);
        
        Yii::info("Setting new password", __CLASS__);
        $token->user->setPassword($newPassword);

        Yii::info("Trying to save user [{$token->user->email}] after password change", __CLASS__);
        if ($token->user->save(false) && $token->delete()) {
            Yii::info("Password of user [{$token->user->email}] successfuly changed", __CLASS__);
        }
        
        Yii::info("Logging in user [{$token->user->email}] after a password change", __CLASS__);
        return Yii::$app->user->login($token->user);
    }
}
