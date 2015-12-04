<?php

namespace nkostadinov\user\tests\_pages;

use yii\codeception\BasePage;

/**
 * Represents the user profile's page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class ProfilePage extends BasePage
{
    public $route = '/user/profile/view';
}
