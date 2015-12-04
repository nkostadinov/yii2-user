<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents the password recovery's page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class PasswordRecoveryPage extends BasePage
{
    public $route = '/user/recovery/request';
}
