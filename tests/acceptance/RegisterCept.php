<?php

use nkostadinov\user\tests\_pages\LoginPage;
use nkostadinov\user\tests\_pages\RegisterPage;
use yii\helpers\Url;

$I = new AcceptanceTester($scenario);
$I->wantTo('see that registration works.');

$loginPage = LoginPage::openBy($I);
$I->see('Login', 'h3');
$I->seeLink('Don\'t have an account yet? Sign up!');

$I->click('Don\'t have an account yet? Sign up!');
$I->seeInCurrentUrl('/user/registration/signup');

