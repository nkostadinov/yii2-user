<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\models\PasswordHistory;
use nkostadinov\user\Module;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Model;

/**
 * Prevents users from creating a password that has already been used in the past.
 * This adds more security to the application.
 */
class PasswordHistoryPolicyBehavior extends Behavior
{
    /** @var integer The number of the password changes, that the system will check. */
    public $lastPasswordChangesCount = 5;

    public function events()
    {
        return [
            Model::EVENT_AFTER_VALIDATE => 'allowPasswordChange',
        ];
    }

    /**
     * Checks whether the user is allowed to change his password.
     *
     * If the password is the same as one of his $lastPasswordChangesCount previous passwords, the user is not allowed.
     *
     * @param Event $event
     */
    public function allowPasswordChange(Event $event)
    {
        $form = $event->sender; // The ChangePasswordForm
        if ($form->oldPassword == $form->newPassword) {
            $form->addError('newPassword', Yii::t(Module::I18N_CATEGORY, 'Passwords must not be the same!'));
            return;
        }

        $security = Yii::$app->security;
        $userModel = $form->getUser();
        $previousPasswords = PasswordHistory::findAllByUserId($userModel->id, $this->lastPasswordChangesCount);
        if (!count($previousPasswords)) { // The password is changed for a first time
            PasswordHistory::createAndSave($userModel->id, $userModel->password_hash); // Save the first password
        } else {
            foreach ($previousPasswords as $passwordHistory) {
                if ($security->validatePassword($form->newPassword, $passwordHistory->password_hash)) {
                    $form->addError('newPassword', Yii::t(Module::I18N_CATEGORY,
                        'Your password is the same as a previous password of yours. For security reasons, please add another password.'));
                    return;
                }
            }
        }
        PasswordHistory::createAndSave($userModel->id, $security->generatePasswordHash($form->newPassword)); // Save the new password
    }
}
