<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents the password recovery's request page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class PasswordRecoveryRequestPage extends BasePage
{
    public $route = '/user/recovery/request';

    public function submitRecoveryForm($email)
    {
        $this->actor->fillField('input[name="RecoveryForm[email]"]', $email);
        $this->actor->click('password-recovery-button');
    }
}
