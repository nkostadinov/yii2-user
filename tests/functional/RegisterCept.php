<?php
use nkostadinov\user\tests\_pages\ConfirmPage;
use nkostadinov\user\tests\_pages\LoginPage;
use nkostadinov\user\tests\_pages\RegisterPage;
use yii\web\ForbiddenHttpException;

//setup vars
$email = 'mail@example.com';

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
$registerPage->register($email, 'test123');
$I->expectTo("to see message that confirmation is sent to $email.");
$I->see("Confirmation mail has been sent to $email.");

//mail has been taken
$registerPage->openBy($I);
$registerPage->register($email, 'test123');
$I->expectTo('to see error that mail address has already been taken.');
$I->see('This email address has already been taken.', 'p.help-block-error');

//check unconfirmed mail error
$loginPage->openBy($I);
$I->assertTrue(
    $I->seeExceptionThrown('yii\web\ForbiddenHttpException', function () use ($loginPage, $email) {
        $loginPage->login($email, 'test123');
    })
, "Didn't see yii\\web\\ForbiddenHttpException when trying to login unconfirmed.");
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
$I->assertTrue(
    $I->seeExceptionThrown('yii\web\BadRequestHttpException', function () use ($I) {
        ConfirmPage::openBy($I);
    })
, "Didn't see yii\\web\\BadRequestHttpException when opening confirm URL without params.");

$confirmPage = ConfirmPage::openBy($I, [ 'user_id' => $user_id, 'code' => $token_code ]);
//$I->assertTrue($confirmPage instanceof ConfirmPage);
$I->expectTo('see successfully confirmed message!');
$I->see('Registration confirmed', 'h1');
$I->see('Your registration is confirmed succesfully!');
//token must be missing
$I->dontSeeInDatabase('token', ['type' => 0, 'user_id' => $user_id]);