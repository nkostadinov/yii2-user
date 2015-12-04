<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents the password reset page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class PasswordResetPage extends BasePage
{
    public $route = '/user/recovery/reset';
}
