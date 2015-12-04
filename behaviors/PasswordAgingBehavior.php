<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\components\User;
use nkostadinov\user\Module;
use Yii;
use yii\base\Behavior;
use yii\web\Application;
use yii\web\ForbiddenHttpException;
use yii\web\UserEvent;

/**
 * Responsible for tracking when a user has changed his/her password for a last time. 
 *
 * If the password hasn't changed for a time longer than $passwordChangeInterval
 * the behavior will invite you to change your password and will log you out of the system.
 */
class PasswordAgingBehavior extends Behavior
{
    /**
     * @var integer The interval of time after which a user will be invited to change his password. The value is in seconds and defaults to 2 months.
     */
    public $passwordChangeInterval = 5184000;

    public function events()
    {
        return [
            User::EVENT_BEFORE_LOGIN => 'execute',
        ];
    }

    /**
     * Calculates the difference between the current time and
     * the value from the password_changed_at field.
     *
     * If the result is bigger than the $passwordChangeInterval, the user will be logged out.
     * If this is a web application, the user will be redirected to the password change page.
     * Else a new ForbiddenHttpException is thrown.
     * 
     * If the value of the 'password_changed_at' field is not set,
     * the current timestamp is set and the login process continues.
     *
     * @throws ForbiddenHttpException (for console applications)
     */
    public function execute(UserEvent $event)
    {
        if (empty($event->identity->password_changed_at)) {
            $event->identity->password_changed_at = time();
            $event->identity->save(false);
        } else if ((time() - $event->identity->password_changed_at) > $this->passwordChangeInterval) {
            $event->isValid = false;
            if (Yii::$app instanceof Application) {
                Yii::$app->response->redirect(Module::$urlRules['changePassword']);
            } else {
                throw new ForbiddenHttpException('The system requires a password change');
            }
        }
    }
}
