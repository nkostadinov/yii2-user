<?php

use nkostadinov\user\tests\_pages\ChangePasswordPage;
use nkostadinov\user\tests\_pages\LoginPage;
use nkostadinov\user\tests\_pages\PasswordRecoveryPage;
use nkostadinov\user\tests\_pages\ProfilePage;
use nkostadinov\user\tests\_pages\RegisterPage;

$I = new FunctionalTester($scenario);
$I->wantTo('test whether all view files are found by the controller actions');

// Login page test
$loginPage = LoginPage::openBy($I);
$I->seeInTitle('Login');
$I->seeElement('#login-form');

// Register page test
RegisterPage::openBy($I);
$I->seeInTitle('Signup');

// Change password page
ChangePasswordPage::openBy($I);
$I->seeInTitle('Change password');
$I->seeElement('#change-password-form');

// Profile page
ProfilePage::openBy($I);
$I->see('profile/view');

// Password recovery page
PasswordRecoveryPage::openBy($I);
$I->seeInTitle('Recover your password');
$I->seeElement('#password-recovery-form');