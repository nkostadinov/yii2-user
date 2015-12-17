<?php

use nkostadinov\user\models\User;
use nkostadinov\user\tests\_pages\LoginPage;
use nkostadinov\user\tests\_pages\LogoutPage;

$I = new FunctionalTester($scenario);
$I->wantTo('see that login works.');

$loginPage = LoginPage::openBy($I);
$I->see('Login', 'h3');

//empty username and password
$loginPage->login('', '');
$I->expectTo('see validations errors');
$I->see('Username cannot be blank.');
$I->see('Password cannot be blank.');

//wrong username and password
$loginPage->login('test', 'test');
$I->expectTo('see validations errors');
$I->see('Incorrect username or password');

//see the forgot password link
$I->seeLink('Forgot password?', '/user/recovery/request');

// Test that the login works
Commons::createUserWithUsername();
$loginPage->login(Commons::TEST_EMAIL, Commons::TEST_PASSWORD);
$I->seeInCurrentUrl('/');

// Logout the user
LogoutPage::openBy($I);

// Log the user in again
$loginPage = LoginPage::openBy($I);
// Test that the login works with the username as well
$loginPage->login(Commons::TEST_USERNAME, Commons::TEST_PASSWORD);
$I->seeInCurrentUrl('/');

User::deleteAll('email = :email', [':email' => Commons::TEST_EMAIL]);
