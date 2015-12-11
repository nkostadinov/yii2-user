<?php

use nkostadinov\user\models\forms\SignupForm;
use nkostadinov\user\validators\PasswordStrengthValidator;
use yii\codeception\TestCase;

class PasswordStrengthTest extends TestCase
{   
    use \Codeception\Specify;

    public $appConfig = '@tests/tests/_app/config/unit.php';
    
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * Tests the 'SIMPLE' preset functionality, meaning: [
     *  'min' => 6,
     *  'upper' => 0,
     *  'lower' => 1,
     *  'digit' => 1,
     *  'special' => 0,
     *  'hasUser' => false,
     *  'hasEmail' => false
     * ]
     */
    public function testSimplePreset()
    {
        Yii::$app->user->passwordStrengthConfig = ['preset' => PasswordStrengthValidator::SIMPLE];
        /* @var $form SignupForm */
        $form = Yii::createObject(Yii::$app->user->registerForm);
        $form->email = Commons::TEST_EMAIL;
        $form->password = 'ABCDEF';
        verify('Having only upper-case characters is not enough', $form->validate())->false();
        
        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There are exactly two errors', count($errors))->equals(2);
        verify('An error for the lower-case exists', strpos($errors[0], 'lower'))->greaterThan(0);
        verify('An error for the digits exists', strpos($errors[1], 'digit'))->greaterThan(0);

        $form->password = 'Innologica1';
        verify('This time everything works fine!', $form->validate())->true();
    }

    /**
     * Tests the 'NORMAL' preset functionality, meaning: [
     *   'min' => 6,
     *   'upper' => 1,
     *   'lower' => 1,
     *   'digit' => 1,
     *   'special' => 1,
     *   'hasUser' => true,
     *   'hasEmail' => true
     * ],
     */
    public function testNormalPreset()
    {
        Yii::$app->user->passwordStrengthConfig = ['preset' => PasswordStrengthValidator::NORMAL];
        /* @var $form SignupForm */
        $form = Yii::createObject(Yii::$app->user->registerForm);
        $form->email = Commons::TEST_EMAIL;
        $form->password = 'nikolay';
        verify('Having only lower-case characters is not enough', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There are exactly three errors', count($errors))->equals(3);
        verify('An error for the upper-case exists', strpos($errors[0], 'upper'))->greaterThan(0);
        verify('An error for the digits exists', strpos($errors[1], 'digit'))->greaterThan(0);
        verify('An error for the special exists', strpos($errors[2], 'special'))->greaterThan(0);

        $form->password = 'Nikolay';
        verify('Having only lower and upper-case characters is not enough', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There are exactly two errors', count($errors))->equals(2);
        verify('An error for the digits exists', strpos($errors[0], 'digit'))->greaterThan(0);
        verify('An error for the special exists', strpos($errors[1], 'special'))->greaterThan(0);
        
        $form->password = 'Niko1ay';
        verify('Having only lower and upper-case characters, and a digit is not enough', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There is exactly one error', count($errors))->equals(1);
        verify('An error for the special exists', strpos($errors[0], 'special'))->greaterThan(0);

        // Adding a password that looks like an email
        $form->password = 'N1k@lay.com';
        verify('Having a password that looks like an email fails', $form->validate())->false();
        
        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There is exactly one error', count($errors))->equals(1);
        verify('An error for the email exists', strpos($errors[0], 'email'))->greaterThan(0);
        
        $form->password = 'Nik@1ay';
        verify('This time everything works fine!', $form->validate())->true();
    }
    
    /**
     * Tests the 'FAIR' preset functionality, meaning: [
     *   'min' => 8,
     *   'upper' => 1,
     *   'lower' => 1,
     *   'digit' => 1,
     *   'special' => 1,
     *   'hasUser' => true,
     *   'hasEmail' => true
     * ],
     */
    public function testFairPreset()
    {
        Yii::$app->user->passwordStrengthConfig = ['preset' => PasswordStrengthValidator::FAIR];
        /* @var $form SignupForm */
        $form = Yii::createObject(Yii::$app->user->registerForm);
        $form->email = Commons::TEST_EMAIL;
        $form->password = 'N1k@lay';
        verify('Having only 7 chars is not enough', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There is exactly one error', count($errors))->equals(1);
        verify('An error for the password length exists', strpos($errors[0], '8 characters'))->greaterThan(0);
        
        $form->password = 'Nik@1ay1';
        verify('This time everything works fine!', $form->validate())->true();
    }

    /**
     * Tests the 'MEDIUM' preset functionality, meaning: [
     *   'min' => 10,
     *   'upper' => 1,
     *   'lower' => 1,
     *   'digit' => 2,
     *   'special' => 1,
     *   'hasUser' => true,
     *   'hasEmail' => true
     * ],
     */
    public function testMediumPreset()
    {
        Yii::$app->user->passwordStrengthConfig = ['preset' => PasswordStrengthValidator::MEDIUM];
        /* @var $form SignupForm */
        $form = Yii::createObject(Yii::$app->user->registerForm);
        $form->email = Commons::TEST_EMAIL;
        $form->password = 'Nik@1ay';
        verify('The validation fails', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There are exactly two errors', count($errors))->equals(2);
        verify('An error for the password length exists', strpos($errors[0], '10 characters'))->greaterThan(0);
        verify('An error for the digits exists', strpos($errors[1], '2 numeric'))->greaterThan(0);

        $form->password = 'Nik@1ay1oo';
        verify('This time everything works fine!', $form->validate())->true();
    }

    /**
     * Tests the 'STRONG' preset functionality, meaning: [
     *   'min' => 12,
     *   'upper' => 2,
     *   'lower' => 2,
     *   'digit' => 2,
     *   'special' => 2,
     *   'hasUser' => true,
     *   'hasEmail' => true
     * ],
     */
    public function testStrongPreset()
    {
        Yii::$app->user->passwordStrengthConfig = ['preset' => PasswordStrengthValidator::STRONG];
        /* @var $form SignupForm */
        $form = Yii::createObject(Yii::$app->user->registerForm);
        $form->email = Commons::TEST_EMAIL;
        $form->password = 'Nik@1ay';
        verify('The validation fails', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There are exactly four errors', count($errors))->equals(4);
        verify('An error for the password length exists', strpos($errors[0], '12 characters'))->greaterThan(0);
        verify('An error for the upper cases exists', strpos($errors[1], '2 upper'))->greaterThan(0);
        verify('An error for the digits', strpos($errors[2], '2 numeric'))->greaterThan(0);
        verify('An error for the special', strpos($errors[3], '2 special'))->greaterThan(0);

        $form->password = 'Nik@1ay1oO#p';
        verify('This time everything works fine!', $form->validate())->true();
    }

    public function testConfigs()
    {
        // Test that different configurations work equally well as the presets
        Yii::$app->user->passwordStrengthConfig = ['min' => 9, 'upper' => 0, 'digit' => 5, 'special' => 3];
        /* @var $form SignupForm */
        $form = Yii::createObject(Yii::$app->user->registerForm);
        $form->email = Commons::TEST_EMAIL;
        $form->password = 'Nik@1ay';
        verify('The validation fails', $form->validate())->false();

        $errors = $form->getErrors('password');
        verify('There are errors on the password field', empty($errors))->false();
        verify('There are exactly three errors', count($errors))->equals(3);
        verify('An error for the password length exists', strpos($errors[0], '9 characters'))->greaterThan(0);
        verify('An error for the digits', strpos($errors[1], '5 numeric'))->greaterThan(0);
        verify('An error for the special', strpos($errors[2], '3 special'))->greaterThan(0);

        $form->password = 'N1k@1a71o0#p%';
        verify('This time everything works fine!', $form->validate())->true();
    }
}
