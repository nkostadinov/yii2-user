<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\interfaces\IUserAccount;
use nkostadinov\user\models\UserAccount;
use Yii;
use yii\authclient\ClientInterface;
use yii\filters\AccessControl;

class SecurityController extends BaseController
{
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

    public function actionLogin()
    {
        $model = \Yii::createObject(Yii::$app->user->loginForm);
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            if(Yii::$app->request->isAjax)
            return $this->renderAjax($this->module->views['login'], [
                'model' => $model,
                'module' => $this->module,
            ]);
            else
            return $this->render($this->module->views['login'], [
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

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
        ];
    }

    public function successCallback(ClientInterface $client)
    {
        $account = \Yii::$app->user->findAccount($client);
        //if account doesnt exists then create it
        if(!isset($account)) {
            $token = $client->getAccessToken();

            $account = new UserAccount();
            $account->provider = $client->getName();
            $account->attributes = json_encode($client->getUserAttributes());
            $account->access_token = $token->token;
            $account->expires = $token->createTimestamp + $token->expireDuration;
            $account->token_create_time = $token->createTimestamp;
            $account->client_id = $client->getUserAttributes()['id'];
            $account->save();
        }

        if(Yii::$app->user->isGuest) { //Create a new user and link account
            if($client instanceof IUserAccount) {
            } else
                Yii::error("Cannot register new user with {$client->name}. You must setup nkostadinov\\clients\\facebook in AuthCollection.");
        } else {
            //add account to user
            $account->link('user', Yii::$app->user->identity);
        }

        return false;
    }

    public function actionChangePassword()
    {
        $behavior = Yii::$app->user->getBehavior('passwordAging');
        $model = Yii::createObject($behavior->changePasswordForm);
        if ($model->load(Yii::$app->request->post()) && $model->changePassword())
            return $this->goBack();

        return $this->render($behavior->changePasswordView, [
            'model' => $model,
        ]);
    }
}
