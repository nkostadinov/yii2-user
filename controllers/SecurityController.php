<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\helpers\Event;
use nkostadinov\user\helpers\Http;
use nkostadinov\user\interfaces\IUserAccount;
use nkostadinov\user\models\User;
use nkostadinov\user\models\UserAccount;
use nkostadinov\user\Module;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\NotSupportedException;
use yii\filters\AccessControl;

class SecurityController extends BaseController
{
     /** Event is triggered before logging the user in. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_LOGIN = 'nkostadinov.user.beforeLogin';
    /** Event is triggered after logging the user in. Triggered with \nkostadinov\user\events\ModelEvent.*/
    const EVENT_AFTER_LOGIN = 'nkostadinov.user.afterLogin';
    /** Event is triggered before logging the user out. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_LOGOUT = 'nkostadinov.user.beforeLogout';
    /** Event is triggered after logging the user out. Triggered with \yii\web\UserEvent. */
    const EVENT_AFTER_LOGOUT = 'nkostadinov.user.afterLogout';
    /** Event is triggered before acquiring the user's email. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_ACQUIRE_EMAIL = 'nkostadinov.user.beforeAcquireEmail';
    /** Event is triggered after acquiring the user's email. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_AFTER_ACQUIRE_EMAIL = 'nkostadinov.user.afterAcquireEmail';
    /** Event is triggered before changing the user's password. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_CHANGE_PASSWORD = 'nkostadinov.user.beforeChangePassword';
    /** Event is triggered after changing the user's password. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_AFTER_CHANGE_PASSWORD = 'nkostadinov.user.afterChangePassword';
    /** Event is triggered before authenticating the user by an OAuth2 authentication. Triggered with \nkostadinov\user\events\AuthEvent. */
    const EVENT_BEFORE_AUTH = 'nkostadinov.user.beforeAuth';
    /** Event is triggered after authenticating the user by an OAuth2 authentication. Triggered with \nkostadinov\user\events\AuthEvent. */
    const EVENT_AFTER_AUTH = 'nkostadinov.user.afterAuth';

    const CLIENT_PARAM = 'client';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'auth', 'change-password', 'acquire-email'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['login', 'auth', 'logout', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
        ];
    }

    public function actionLogin()
    {
        Yii::info('User is entering the login page', __CLASS__);

        $model = Yii::createObject(Yii::$app->user->loginForm);

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::info("User [$model->username] is successfuly logged in", __CLASS__);
            $this->trigger(self::EVENT_AFTER_LOGIN, $event);
            return $this->goBack();
        } else {
            if(Yii::$app->request->isAjax) {
                return $this->renderAjax($this->module->loginView, [
                    'model' => $model,
                    'module' => $this->module,
                ]);
            }
            return $this->render($this->module->loginView, [
                'model' => $model,
                'module' => $this->module,
            ]);
        }
    }

    public function actionLogout()
    {
        $email = Yii::$app->user->identity->email;
        Yii::info("Logging out user [$email]", __CLASS__);

        $event = Event::createUserEvent(Yii::$app->user->identity);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        Yii::$app->user->logout();

        Yii::info("User [$email] is successfuly logged out", __CLASS__);
        $this->trigger(self::EVENT_AFTER_LOGOUT, $event);

        return $this->goHome();
    }

    public function successCallback(ClientInterface $client)
    {
        if(!$client instanceof IUserAccount) {
            throw new NotSupportedException('Your client must extend the IUserInterface.');
        }
        
        Yii::info("User [$client->id][$client->userId] is entering the third-party registration page", __CLASS__);
        $account = UserAccount::findByClient($client);
        if(empty($account)) { // If account doesn't exist, create it
            Yii::info("Creating user account for user [$client->id][$client->userId]", __CLASS__);
            $account = UserAccount::createAndSave($client);
        }

        $event = Event::createAuthEvent($account, $client);
        $this->trigger(self::EVENT_BEFORE_AUTH, $event);

        $result = true;
        if(!$account->user) { // Create a new user or link account to an existing user
            if (Yii::$app->user->isGuest) { // This means the user comes for a first time or has a user created by a regular login or another client
                $email = $client->getEmail();
                if (is_null($email)) { // Sometimes the email cannot be fetched from the client
                    Yii::info("Unable to fetch the email of account [$client->id][$client->userId]", __CLASS__);
                    Yii::$app->session->set(self::CLIENT_PARAM, $client);
                    $result = $this->redirect(["/{$this->module->id}/security/acquire-email"]); // Redirect to a page where the user must add an email
                } else {
                    $result = $this->createUser($client, $account, $email);
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

        $this->trigger(self::EVENT_AFTER_AUTH, $event);
        return $result;
    }

    public function actionAcquireEmail()
    {
        Yii::info("User is entering the acquire email page", __CLASS__);
        $model = Yii::createObject(Yii::$app->user->acquireEmailForm);

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_ACQUIRE_EMAIL, $event);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::info("User has entered email [$model->email]", __CLASS__);

            $client = Yii::$app->session->get(self::CLIENT_PARAM);
            Yii::$app->session->remove(self::CLIENT_PARAM);
            
            $account = UserAccount::findByClient($client);
            $response = $this->createUser($client, $account, $model->email);

            $this->trigger(self::EVENT_AFTER_ACQUIRE_EMAIL, $event);
            return $response;
        }

        return $this->render($this->module->acquireEmailView, [
            'model' => $model
        ]);
    }

    public function actionChangePassword()
    {
        Yii::info("User is entering the change password page", __CLASS__);

        $model = Yii::createObject(Yii::$app->user->changePasswordForm);
        $model->scenario();

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_CHANGE_PASSWORD, $event);

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            $this->trigger(self::EVENT_AFTER_CHANGE_PASSWORD, $event);
            return $this->goBack();
        }

        return $this->render($this->module->changePasswordView, [
            'model' => $model,
        ]);
    }

    private function createUser(IUserAccount $client, $account, $email)
    {
        Yii::info("Trying to create a new user for account [$client->id][$client->userId][$email]", __CLASS__);

        $user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'],
            ['email' => $email]);
        if (!$user) {
            Yii::info("Creating a new user for account [$client->id][$client->userId][$email]", __CLASS__);
            // Create a new user
            $user = new User();
            $user->email = $email;
            $user->name = $client->getRealName();
            $user->register_ip = Http::getUserIP();
            $user->save();

            Yii::info("Linking user [$email] to account [$client->id][$client->userId]", __CLASS__);
            $account->link('user', $user);
        } else {
            Yii::info("User already exists for account [$client->id][$client->userId][$email]. Redirecting user to the login page", __CLASS__);
            // User already exists
            Yii::$app->session->setFlash('warning',
                Yii::t(Module::I18N_CATEGORY, 'This email is already taken. If you want to link your account, please login first!'));
            return $this->redirect(["/{$this->module->id}/security/login"]);
        }

        if (Yii::$app->user->login($user)) {
            Yii::info("Logging in user [$client->id][$client->userId][$email]", __CLASS__);
            return $this->goHome();
        }

        Yii::error("Unable to login user [$client->id][$client->userId][$email]", __CLASS__);
        return $this->goBack();
    }
}
