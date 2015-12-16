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
        $model = Yii::createObject(Yii::$app->user->loginForm);

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
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
        $event = Event::createUserEvent(Yii::$app->user->identity);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        Yii::$app->user->logout();

        $this->trigger(self::EVENT_AFTER_LOGOUT, $event);

        return $this->goHome();
    }

    public function successCallback(ClientInterface $client)
    {
        if(!$client instanceof IUserAccount)
            throw new NotSupportedException(Yii::t(Module::I18N_CATEGORY, 'Your client must extend the IUserInterface.'));
        
        $account = UserAccount::findByClient($client);
        if(empty($account)) // If account doesn't exist, create it
            $account = UserAccount::createAndSave($client);

        $event = Event::createAuthEvent($account, $client);
        $this->trigger(self::EVENT_BEFORE_AUTH, $event);

        $result = true;
        if(!$account->user) { // Create a new user or link account to an existing user
            if (Yii::$app->user->isGuest) { // This means the user comes for a first time or has a user created by a regular login or another client
                $email = $client->getEmail();
                if (is_null($email)) { // Sometimes the email cannot be fetched from the client
                    Yii::$app->session->set(self::CLIENT_PARAM, $client);
                    $result = $this->redirect(["/{$this->module->id}/security/acquire-email"]); // Redirect to a page where the user must add an email
                } else {
                    $result = $this->createAccount($client, $account, $email);
                }
            } else { // Link account to user
                // This means the user is logged in through a regular login or another client. Needs to be linked.
                $account->link('user', Yii::$app->user->identity);
            }
        } else if (Yii::$app->user->isGuest) {
            $result = Yii::$app->user->login($account->user);
        }

        $this->trigger(self::EVENT_AFTER_AUTH, $event);
        return $result;
    }

    public function actionAcquireEmail()
    {
        $model = Yii::createObject(Yii::$app->user->acquireEmailForm);

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_ACQUIRE_EMAIL, $event);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $client = Yii::$app->session->get(self::CLIENT_PARAM);
            Yii::$app->session->remove(self::CLIENT_PARAM);
            
            $account = UserAccount::findByClient($client);
            $response = $this->createAccount($client, $account, $model->email);

            $this->trigger(self::EVENT_AFTER_ACQUIRE_EMAIL, $event);
            return $response;
        }

        return $this->render($this->module->acquireEmailView, [
            'model' => $model
        ]);
    }

    public function actionChangePassword()
    {
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

    private function createAccount(IUserAccount $client, $account, $email)
    {
        $user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'],
            ['email' => $email]);
        if (!$user) {
            // Create a new user
            $user = new User();
            $user->email = $email;
            $user->name = $client->getRealName();
            $user->register_ip = Http::getUserIP();
            $user->save();
            
            $account->link('user', $user);
        } else {
            // User already exists
            Yii::$app->session->setFlash('warning',
                Yii::t(Module::I18N_CATEGORY, 'This email is already taken. If you want to link your account, please login first!'));
            return $this->redirect(["/{$this->module->id}/security/login"]);
        }

        if (Yii::$app->user->login($user)) {
            return $this->goHome();
        }

        return $this->goBack();
    }
}
