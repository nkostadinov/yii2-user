<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\models\Token;
use Yii;
use yii\web\NotFoundHttpException;

class RegistrationController extends BaseController
{
    public function actionConfirm($code)
    {
        $token = Token::findByCode($code, Token::TYPE_CONFIRMATION);
        $token->user->confirm($code);
        
        return $this->render($this->module->confirmView);
    }

    public function actionSignup()
    {
        if (!$this->module->allowRegistration)
            throw new NotFoundHttpException("Registration disabled!");

        $model = Yii::createObject(Yii::$app->user->registerForm);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->signup()) {
                if(Yii::$app->user->enableConfirmation)
                    return $this->renderContent(\Yii::t('app.user', 'Confirmation mail has been sent to {0}.', [$model->email]));
                
                return $this->goHome();
            }
        }

        return $this->render($this->module->registerView, [
            'model' => $model,
        ]);
    }
}
