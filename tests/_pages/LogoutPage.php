<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents logout page
 * @property \FunctionalTester $actor
 */
class LogoutPage extends BasePage
{
    public $route = '/user/security/logout';
}
