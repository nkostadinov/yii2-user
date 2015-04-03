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

}
