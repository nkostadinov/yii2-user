<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents the change password page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class ChangePasswordPage extends BasePage
{
    public $route = '/user/security/change-password';

    public function changePassword($newPassword, $newPasswordRepeat)
    {
        $this->actor->fillField('input[name="ChangePasswordForm[newPassword]"]', $newPassword);
        $this->actor->fillField('input[name="ChangePasswordForm[newPasswordRepeat]"]', $newPasswordRepeat);
        $this->actor->click('change-password-button');
    }
}
