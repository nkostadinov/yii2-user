<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\components\User;
use Yii;
use yii\base\Behavior;
use yii\web\ForbiddenHttpException;

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
    public $passwordChangeInterval = 60 * 60 * 24 * 30 * 2;

    /**
     * @var array The route where the user will be redirected for a password change.
     */
    public $changePasswordUrl = ['user/security/change-password'];

    /**
     * @var type The form used for changing the old password.
     */
    public $changePasswordForm = 'nkostadinov\user\models\forms\ChangePasswordForm';

    /**
     * @var type The form used for changing the old password.
     */
    public $changePasswordView = '@nkostadinov/user/views/security/change_password';

    public function events()
    {
        return [
            User::EVENT_AFTER_LOGIN => 'execute',
        ];
    }

    /**
     * Calculates the difference between the current time and
     * the value from the password_changed_at field.
     *
     * If the result is bigger than the $passwordChangeInterval, the user will be logged out.
     *
     * If this is a web application, the user will be redirected to the password change page.
     * Else a new ForbiddenHttpException is thrown.
     *
     * If the value of the 'password_changed_at' field is not set,
     * the current timestamp is set and the login process continues.
     *
     * @throws ForbiddenHttpException
     */
    public function execute()
    {
        $identity = Yii::$app->user->identity;
        if (empty($identity->password_changed_at)) {
            $identity->password_changed_at = time();
            $identity->save(false);
        } else if ((time() - $identity->password_changed_at) > $this->passwordChangeInterval) {
            Yii::$app->user->logout();
            if (Yii::$app instanceof \yii\web\Application) {
                Yii::$app->response->redirect($this->changePasswordUrl);
            } else {
                throw new ForbiddenHttpException('The system requires a password change');
            }
        }
    }
}
