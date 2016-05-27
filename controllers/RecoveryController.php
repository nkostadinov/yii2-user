<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 29.03.2015
 * Time: 19:08 ч.
 */

namespace nkostadinov\user\controllers;

use nkostadinov\user\models\forms\ResetPasswordForm;
use nkostadinov\user\models\Token;
use nkostadinov\user\Module;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

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
        Yii::info("User is entering the recovery page", __CLASS__);
        
        $model = Yii::createObject(Yii::$app->user->recoveryForm);
        if ($model->load(Yii::$app->request->post()) && $model->sendRecoveryMessage()) {
            Yii::info("Message successfuly sent to [$model->email]", __CLASS__);
            Yii::$app->session->setFlash('info', Yii::t(Module::I18N_CATEGORY, 'An email has been sent with instructions for resetting your password'));
            return $this->render('message', [
                'title'  => Yii::t(Module::I18N_CATEGORY, 'Recovery message sent'),
                'module' => $this->module,
            ]);
        }

        return $this->render($this->module->requestView, [
            'model' => $model,
        ]);
    }

    public function actionReset($code)
    {
        Yii::info("User is trying to reset password by using [$code]", __CLASS__);
        $model = Yii::createObject([
            'class' => Yii::$app->user->resetPasswordForm,
            'token' => $code,
        ]);
        if ($model->reset()) {
            return $this->redirect(Url::to(['/']));
        }
        return $this->render($this->module->resetPasswordView, ['model' => $model]);
    }
}
