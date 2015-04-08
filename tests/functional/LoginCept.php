<?php

use nkostadinov\user\tests\_pages\LoginPage;

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
