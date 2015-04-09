<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class RegisterPage extends BasePage
{
    public $route = '/user/registration/signup';

    public function register($email, $password)
    {
        $this->actor->fillField('input[name="SignupForm[email]"]', $email);
        $this->actor->fillField('input[name="SignupForm[password]"]', $password);
        $this->actor->click('signup-button');
    }
}
