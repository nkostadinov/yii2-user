<?php

use nkostadinov\user\models\User;
use nkostadinov\user\tests\_pages\ChangePasswordPage;
use nkostadinov\user\tests\_pages\LoginPage;

/**
 * Tests the regular password change, where the user does a password change by his/hers desire.
 */
class ChangePasswordCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
        User::deleteAll('email = :email', [':email' => Commons::TEST_EMAIL]);
    }

    public function testChangePassword(FunctionalTester $I)
    {
        $I->amGoingTo('test the change password functionality');

        // Create one user
        $user = Commons::createUser();

        $I->amGoingTo('login a user');

        $loginPage = LoginPage::openBy($I);
        $loginPage->login(Commons::TEST_EMAIL, Commons::TEST_PASSWORD);

        $changePasswordPage = ChangePasswordPage::openBy($I);
        $I->see('Change password');
        $I->seeElement('#changepasswordform-newpassword');
        $I->seeElement('#changepasswordform-newpasswordrepeat');
        $I->dontSeeElement('#changepasswordform-email');
        $I->dontSeeElement('#changepasswordform-oldpassword');

        $I->amGoingTo('try to change the password with two different passwords for the new password and the new password repeat fields');
        $changePasswordPage->changePassword('123123', '234234');

        $I->expect('the form will catch the difference');
        $I->see('The new passwords are not the same.');

        $I->amGoingTo('test adding new password with length lower than the default length');
        $changePasswordPage->changePassword('123', '123');

        $I->expect('the form will warn the user');
        $I->see('New password should contain at least 6 characters');
        $I->see('New password repeat should contain at least 6 characters');

        $I->amGoingTo('change the password of the user properly');
        $changePasswordPage->changePassword('123123', '123123');

        $I->expect('that this time everything will be ok and the user will be redirected to the home page');
        $user->refresh();
        $I->assertNotNull($user->password_changed_at);
        $I->seeInCurrentUrl('/');
    }
}
