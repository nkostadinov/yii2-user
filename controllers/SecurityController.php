<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\helpers\Http;
use nkostadinov\user\interfaces\IUserAccount;
use nkostadinov\user\models\forms\AcquireEmailForm;
use nkostadinov\user\models\User;
use nkostadinov\user\models\UserAccount;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\NotSupportedException;
use yii\filters\AccessControl;

class SecurityController extends BaseController
{
    const CLIENT_PARAM = 'client';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
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
        $model = \Yii::createObject(Yii::$app->user->loginForm);
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            if(Yii::$app->request->isAjax)
            return $this->renderAjax($this->module->loginView, [
                'model' => $model,
                'module' => $this->module,
            ]);
            else
            return $this->render($this->module->loginView, [
                'model' => $model,
                'module' => $this->module,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function successCallback(ClientInterface $client)
    {
        if(!$client instanceof IUserAccount)
            throw new NotSupportedException('Your client must extend the IUserInterface. The valid clients are in the nkostadinov\user\accounts namespace');
        
        $account = UserAccount::findByClient($client);
        if(empty($account)) // If account doesn't exist, create it
            $account = UserAccount::createAndSave($client);

        if(!$account->user) { // Create a new user or link account to an existing user
            if (Yii::$app->user->isGuest) { // This means the user comes for a first time or has a user created by a regular login or another client
                $email = $client->getEmail();
                if (is_null($email)) { // Sometimes the email cannot be fetched from the client
                    Yii::$app->session->set(self::CLIENT_PARAM, $client);
                    return $this->redirect('/user/security/acquire-email'); // Redirect to a page where the user must add an email
                }

                return $this->createAccount($client, $account, $email);
            } else { // Link account to user
                // This means the user is logged in through a regular login or another client. Needs to be linked.
                $account->link('user', Yii::$app->user->identity);
            }
        } else if (Yii::$app->user->isGuest) {
            return Yii::$app->user->login($account->user);
        }

        return true;
    }

    public function actionAcquireEmail()
    {
        $model = Yii::createObject(Yii::$app->user->acquireEmailForm);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $client = Yii::$app->session->get(self::CLIENT_PARAM);
            Yii::$app->session->remove(self::CLIENT_PARAM);
            $account = UserAccount::findByClient($client);
            
            return $this->createAccount($client, $account, $model->email);
        }

        return $this->render($this->module->acquireEmailView, [
            'model' => $model
        ]);
    }

    public function actionChangePassword()
    {
        $model = Yii::createObject(Yii::$app->user->changePasswordForm);
        $model->scenario();
        if ($model->load(Yii::$app->request->post()) && $model->changePassword())
            return $this->goBack();

        return $this->render($this->module->changePasswordView, [
            'model' => $model,
        ]);
    }

    private function createAccount(IUserAccount $client, $account, $email)
    {
        $user = User::findByEmail($email);
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
            Yii::$app->session->setFlash('warning', 'This email is already taken. In order to link your account, please login first!');

            return $this->redirect(['/user/security/login']);
        }

        if (Yii::$app->user->login($user)) {
            return $this->goHome();
        }

        return $this->goBack();
    }
}
