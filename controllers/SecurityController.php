<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\components\User;
use nkostadinov\user\exceptions\DuplicatedUserException;
use nkostadinov\user\exceptions\MissingEmailException;
use nkostadinov\user\helpers\Event;
use nkostadinov\user\models\UserAccount;
use Yii;
use yii\authclient\ClientInterface;
use yii\filters\AccessControl;
use yii\web\Response;

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
    /** Event is triggered before acquiring the user's password. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_ACQUIRE_PASSWORD = 'nkostadinov.user.beforeAcquirePassword';
    /** Event is triggered after acquiring the user's password. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_AFTER_ACQUIRE_PASSWORD = 'nkostadinov.user.afterAcquirePassword';
    /** Event is triggered before changing the user's password. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_CHANGE_PASSWORD = 'nkostadinov.user.beforeChangePassword';
    /** Event is triggered after changing the user's password. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_AFTER_CHANGE_PASSWORD = 'nkostadinov.user.afterChangePassword';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'denyCallback' => function($rule, $action) {
                    if (!Yii::$app->user->isGuest && $action->id == 'login') {
                        $this->redirect(Yii::$app->homeUrl);
                    }
                },
                'rules' => [
                    [
                        'actions' => ['login', 'auth', 'change-password', 'acquire-email', 'acquire-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['auth', 'logout', 'change-password'],
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
        Yii::info("User [$client->id][$client->userId] is entering the third-party registration page", __CLASS__);
        try {
            $result = Yii::$app->user->oAuthAuthentication($client);
        } catch (MissingEmailException $ex) {
            // Redirect to a page where the user must add an email
            Yii::$app->session->set(User::CLIENT_PARAM, $client);
            
            return $this->redirect(['acquire-email']);
        } catch (DuplicatedUserException $ex) {
            // Redirect to a page where the user must add password
            Yii::$app->session->set(User::CLIENT_PARAM, $client);
            Yii::$app->session->set('email', $client->email);

            return $this->redirect(['acquire-password']);
        }
        
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

            $client = Yii::$app->session->get(User::CLIENT_PARAM);
            $account = UserAccount::findByClient($client);

            try {
                $response = Yii::$app->user->createUserByOAuthIfNotExists($client, $account, $model->email);
                Yii::$app->session->remove(User::CLIENT_PARAM);
            } catch (DuplicatedUserException $ex) {
                Yii::$app->session->set('email', $model->email);
                $response = $this->redirect(['acquire-password']);
            }

            $this->trigger(self::EVENT_AFTER_ACQUIRE_EMAIL, $event);
            return $response instanceof Response ? $response : $this->goBack();
        }

        return $this->render($this->module->acquireEmailView, [
            'model' => $model
        ]);
    }

    public function actionAcquirePassword()
    {
        Yii::info("User is entering the acquire password page", __CLASS__);

        $model = Yii::createObject(Yii::$app->user->loginForm);
        $model->username = Yii::$app->session->get('email');
        $model->rememberMe = false;

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_ACQUIRE_PASSWORD, $event);

        if ($model->load(Yii::$app->request->post())) {
            Yii::info("User [$model->username] has entered password and is trying to link the accounts", __CLASS__);
            if ($model->login()) {
                $client = Yii::$app->session->get(User::CLIENT_PARAM);
                $account = UserAccount::findByClient($client);
                $user = $model->getUser();
                
                $account->link('user', $user);

                Yii::$app->session->remove(User::CLIENT_PARAM);
                Yii::$app->session->remove('email');

                $this->trigger(self::EVENT_AFTER_ACQUIRE_PASSWORD, $event);
                
                return $this->goHome();
            }
        }

        return $this->render($this->module->acquirePasswordView, [
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
}
