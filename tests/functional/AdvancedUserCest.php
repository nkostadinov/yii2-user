<?php

use nkostadinov\user\models\User;
use nkostadinov\user\tests\_pages\RegisterPage;

class AdvancedUserCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
        User::deleteAll('email = :email', [':email' => Commons::TEST_EMAIL]);
    }

    /**
     * Checks that the minimum password requirement is working as expected (IS-21).
     *
     * @param FunctionalTester $I
     */
    public function testTheMinimumPasswordLength(FunctionalTester $I)
    {
        // assert that the property exists
        $I->assertTrue(isset(Yii::$app->user->minPasswordLength));
        // assert that the default value of the property is 6
        $I->assertEquals(6, Yii::$app->user->minPasswordLength);
        
        // try to register a user with a shorter password
        $registerPage = RegisterPage::openBy($I);
        $registerPage->register(Commons::TEST_EMAIL, '12345');
        // it must fail
        $I->see('Password should contain at least 6 characters.');
        $I->dontSeeRecord(User::className(), ['email' => Commons::TEST_EMAIL]);

        // try to register a user with a correct password length
        $registerPage->register(Commons::TEST_EMAIL, 'Innologica!23');
        // it must pass
        $I->seeRecord(User::className(), ['email' => Commons::TEST_EMAIL]);
    }
}
