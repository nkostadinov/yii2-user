<?php

namespace nkostadinov\user\controllers;

use Yii;
use yii\filters\AccessControl;

/**
 * A base profile controller.
 *
 * @author Nikolay Traykov
 * @package nkostadinov\user\controllers
 */
class ProfileController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionView()
    {
        Yii::info('User ['. Yii::$app->user->identity->email .'] is entering the profile page', __CLASS__);
        return $this->render($this->module->profileView);
    }
}
