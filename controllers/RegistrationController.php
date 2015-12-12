<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\helpers\Event;
use nkostadinov\user\models\Token;
use Yii;
use yii\web\NotFoundHttpException;

class RegistrationController extends BaseController
{
    /** Event is triggered before signing the user. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_BEFORE_SIGNUP = 'nkostadinov.user.beforeSignup';
    /** Event is triggered after signing the user. Triggered with \nkostadinov\user\events\ModelEvent. */
    const EVENT_AFTER_SIGNUP = 'nkostadinov.user.afterSignup';

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

        $event = Event::createModelEvent($model);
        $this->trigger(self::EVENT_BEFORE_SIGNUP, $event);

        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            $this->trigger(self::EVENT_AFTER_SIGNUP, $event);
            if(Yii::$app->user->enableConfirmation)
                return $this->renderContent(\Yii::t('app.user', 'Confirmation mail has been sent to {0}.', [$model->email]));

            return $this->goHome();
        }

        return $this->render($this->module->registerView, [
            'model' => $model,
        ]);
    }
}
