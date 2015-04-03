<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 29.03.2015
 * Time: 19:08 ч.
 */

namespace nkostadinov\user\controllers;

use yii\filters\AccessControl;

/**
 * Recovery controller is used to recover forgotten password. It is sent via email.
 *
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * @package nkostadinov\user\controllers
 */
class RecoveryController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => $this->module->allowPasswordRecovery,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionRequest()
    {
        $model = \Yii::createObject(\Yii::$app->user->recoveryForm);
        $model->scenario = 'request';

        if ($model->load(\Yii::$app->request->post()) && $model->sendRecoveryMessage()) {
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Recovery message sent'),
                'module' => $this->module,
            ]);
        }

        return $this->render($this->module->views['request'], [
            'model' => $model,
        ]);
    }
}