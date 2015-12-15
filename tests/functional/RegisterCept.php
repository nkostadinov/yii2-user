<?php

use nkostadinov\user\models\User;
use nkostadinov\user\tests\_pages\ConfirmPage;
use nkostadinov\user\tests\_pages\LoginPage;
use nkostadinov\user\tests\_pages\RegisterPage;

//setup vars
$email = Commons::TEST_EMAIL;

$I = new FunctionalTester($scenario);
$I->wantTo('see that registration works.');

$loginPage = LoginPage::openBy($I);
$I->see('Login', 'h3');
$I->seeLink('Don\'t have an account yet? Sign up!');

$I->click('Don\'t have an account yet? Sign up!');
$I->seeInCurrentUrl('/user/registration/signup');

$registerPage = RegisterPage::openBy($I);
$I->see('Signup', 'h3.panel-title');

//check wrong mail
$registerPage->register('wrong_mail', '');
$I->expectTo('to see error that mail is invalid.');
$I->see('Email is not a valid email address.');
$I->see('Password cannot be blank.');

//check empty password
$registerPage->register($email, '');
$I->expectTo('to see error that password cannot be blank.');
$I->see('Password cannot be blank.');

//successful registration
$registerPage->register($email, 'Test!23');
$I->expectTo("to see message that confirmation is sent to $email.");
$I->see("Confirmation mail has been sent to $email.");

//mail has been taken
$registerPage->openBy($I);
$registerPage->register($email, 'test123');
$I->expectTo('to see error that mail address has already been taken.');
$I->see('This email address has already been taken.', 'p.help-block-error');

//check unconfirmed mail error
//TODO: this fails on travis beacause of buggy phpunit, will enable when fixed
$loginPage->openBy($I);
//$I->assertTrue(
//    $I->seeExceptionThrown('yii\web\ForbiddenHttpException', function () use ($loginPage, $email) {
//        $loginPage->login($email, 'test123');
//    })
//, "I see yii\\web\\ForbiddenHttpException when trying to login unconfirmed.");

//The exception is handled therefore I cannot see the items below !
//$I->seeResponseCodeIs(403); //forbidden
//$I->expectTo('see error that you cannot login without confirming your account.');
//$I->see('Unconfirmed account are not allowed to login');

//check database status
$I->seeInDatabase('user', ['email' => $email, 'status' => 1, 'confirmed_on' => null]);
$user_id = $I->grabFromDatabase('user', 'id', ['email' => $email]);
$I->seeInDatabase('token', ['type' => 0, 'user_id' => $user_id]);
$token_code = $I->grabFromDatabase('token', 'code', ['type' => 0, 'user_id' => $user_id]);


//Confirmation tests
//TODO: this fails on travis beacause of buggy phpunit, will enable when fixed
//$I->assertTrue(
//    $I->seeExceptionThrown('yii\web\BadRequestHttpException', function () use ($I) {
//        ConfirmPage::openBy($I);
//    })
//, "I see yii\\web\\BadRequestHttpException when opening confirm URL without params.");

$confirmPage = ConfirmPage::openBy($I, ['code' => $token_code]);
//$I->assertTrue($confirmPage instanceof ConfirmPage);
$I->expectTo('see successfully confirmed message!');
$I->see('Registration confirmed', 'h1');
$I->see('Your registration is confirmed succesfully!');
//token must be missing
$I->dontSeeInDatabase('token', ['type' => 0, 'user_id' => $user_id]);

User::deleteAll('email = :email', [':email' => $email]);