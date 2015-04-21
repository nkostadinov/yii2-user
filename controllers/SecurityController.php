<?php

namespace nkostadinov\user\controllers;

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
        $attributes = $client->getUserAttributes();

        // user login or signup comes here
        return false;
    }
}
