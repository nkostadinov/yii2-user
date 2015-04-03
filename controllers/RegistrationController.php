<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\models\User;
use Yii;
use yii\web\NotFoundHttpException;

class RegistrationController extends BaseController
{
    public function actionConfirm($user_id, $code)
    {
        $user = User::findOne($user_id);
        $user->confirm($code);
        return $this->render($this->module->views['confirm']);
    }

    public function actionSignup()
    {
        if (!$this->module->allowRegistration)
            throw new NotFoundHttpException("Registration disabled!");

        $model = Yii::createObject(Yii::$app->user->registerForm);

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if(Yii::$app->user->enableConfirmation)
                    return $this->renderContent(\Yii::t('app.user', 'Confirmation mail has been sent to {0}.', [ $model->email ]));
                return $this->goHome();
            }
        }

        return $this->render($this->module->views['register'], [
            'model' => $model,
        ]);
    }

}
