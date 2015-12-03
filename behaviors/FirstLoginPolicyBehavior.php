<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\Module;
use Yii;
use yii\base\Behavior;
use yii\web\ForbiddenHttpException;
use yii\web\User;
use yii\web\UserEvent;

/**
 * Requires the user to change his password on a first login or when required.
 */
class FirstLoginPolicyBehavior extends Behavior
{
    public function events()
    {
        return [
            User::EVENT_BEFORE_LOGIN => 'passwordChangeChecker',
        ];
    }

    /**
     * @param UserEvent $event
     * @throws ForbiddenHttpException If the user is required to change password (for console applications)
     */
    public function passwordChangeChecker(UserEvent $event)
    {
        if ($event->identity->require_password_change) {
            $event->isValid = false;
            if (Yii::$app instanceof \yii\web\Application) {
                Yii::$app->response->redirect(Module::$urlRules['changePassword']);
            } else {
                throw new ForbiddenHttpException('The system requires a password change');
            }
        }
    }
}