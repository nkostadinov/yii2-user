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
            Yii::info("The system doesn't allow a password change because the current "
                . "password of the user is the same as the new one!", __CLASS__);
            $form->addError('newPassword', Yii::t(Module::I18N_CATEGORY, 'Passwords must not be the same!'));
            return;
        }

        $security = Yii::$app->security;
        $userModel = $form->getUser();
        $previousPasswords = PasswordHistory::findAllByUserId($userModel->id, $this->lastPasswordChangesCount);
        if (!count($previousPasswords)) { // The password is changed for a first time
            Yii::info("Save the first password of the user to the password history table", __CLASS__);
            PasswordHistory::createAndSave($userModel->id, $userModel->password_hash); // Save the first password
        } else {
            foreach ($previousPasswords as $passwordHistory) {
                if ($security->validatePassword($form->newPassword, $passwordHistory->password_hash)) {
                    Yii::info("The system doesn't allow a password change, because "
                        . "the current password is the same as one of the previous passwords of the user!", __CLASS__);
                    $form->addError('newPassword', Yii::t(Module::I18N_CATEGORY,
                        'Your password is the same as a previous password of yours. For security reasons, please add another password.'));
                    return;
                }
            }
        }
        Yii::info("Save the new password of the user to the password history table", __CLASS__);
        PasswordHistory::createAndSave($userModel->id, $security->generatePasswordHash($form->newPassword)); // Save the new password
    }
}
