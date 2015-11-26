<?php

use nkostadinov\user\models\User;
use nkostadinov\user\tests\_pages\RegisterPage;

class AdvancedAuthenticationCest
{
    const TEST_EMAIL = 'test@innologica.com';
    
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
        User::deleteAll('email = :email', [':email' => self::TEST_EMAIL]);
    }

    /**
     * Checks that the minimum password requirement is working as expected (IS-21).
     *
     * @param FunctionalTester $I
     */
    public function minimumPasswordLengthTest(FunctionalTester $I)
    {
        // assert that the property exists
        $I->assertTrue(isset(Yii::$app->user->minPasswordLength));
        // assert that the default value of the property is 6
        $I->assertEquals(6, Yii::$app->user->minPasswordLength);
        
        // try to register a user with a shorter password
        $registerPage = RegisterPage::openBy($I);
        $registerPage->register(self::TEST_EMAIL, '12345');
        // it must fail
        $I->see('Password should contain at least 6 characters.');
        $I->dontSeeRecord(User::className(), ['email' => self::TEST_EMAIL]);

        // try to register a user with a correct password length
        $registerPage->register(self::TEST_EMAIL, '123456');
        // it must pass
        $I->seeRecord(User::className(), ['email' => self::TEST_EMAIL]);
    }
}
