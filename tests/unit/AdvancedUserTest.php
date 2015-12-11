<?php

use nkostadinov\user\behaviors\PasswordHistoryPolicyBehavior;
use nkostadinov\user\models\forms\ChangePasswordForm;
use nkostadinov\user\models\PasswordHistory;
use nkostadinov\user\models\User;
use yii\codeception\TestCase;
use yii\web\ForbiddenHttpException;

class AdvancedUserTest extends TestCase
{   
    use \Codeception\Specify;

    const ATTR_PASSWORD_CHANGED_AT = 'password_changed_at';
    
    const ATTR_PASSWORD_HISTORY = 'password_history';    
    const PASSWORD_HISTORY_MODEL = 'nkostadinov\user\models\PasswordHistory';

    const ATTR_LOGIN_ATTEMPTS = 'login_attempts';
    const ATTR_LOCKED_UNTIL = 'locked_until';

    const ATTR_REQUIRE_PASSWORD_CHANGE = 'require_password_change';

    public $appConfig = '@tests/tests/_app/config/unit.php';
    
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        User::deleteAll('email = :email', [':email' => Commons::TEST_EMAIL]);
        parent::tearDown();
    }

    public function testPasswordAging()
    {
        // Asure that everything is configured properly
        verify('Check that the advanced directory exists', is_dir(Commons::ADVANCED_MIGRATIONS_DIR))->true();

        $files = scandir(Commons::ADVANCED_MIGRATIONS_DIR);
        $result = preg_grep('/'. self::ATTR_PASSWORD_CHANGED_AT .'/', $files);
        verify('Check that the migration exists', $result)->notEmpty();

        verify('Check that the field is added to the table (the migration is run)',
            (new User())->hasAttribute(self::ATTR_PASSWORD_CHANGED_AT))->true();

        // Behavior validations
        $behavior = Yii::$app->user->attachBehavior('passwordAging', 'nkostadinov\user\behaviors\PasswordAgingBehavior');
        verify('Check that the behavior exists', $behavior)->notNull();

        verify('Check that passwordChangeInterval field exists', isset($behavior->passwordChangeInterval))->true();
        verify('Check that the default value of passwordChangeInterval is set to two months (in seconds)',
            $behavior->passwordChangeInterval)->equals(60 * 60 * 24 * 30 * 2);

        // Create one user
        $identity = Commons::createUser();
        verify('Asure that the password_changed_at field is empty', $identity->password_changed_at)->null();

        // Login for a first time
        Yii::$app->user->login($identity);
        verify('After the first login, the password_changed_at field must be automaticaly set', $identity->password_changed_at)->notNull();
        Yii::$app->user->logout();
        
        // Set the password_changed_at value to a value older than two months
        $identity->setAttribute(self::ATTR_PASSWORD_CHANGED_AT, strtotime('-3 months'));
        $identity->save('false');

        verify('The login is unsuccessful', Yii::$app->user->login($identity))->false();

        // Now set the value of password_changed_at field to 1 month
        $identity->setAttribute(self::ATTR_PASSWORD_CHANGED_AT, strtotime('-1 month'));
        $identity->save('false');

        verify('The login is successful', Yii::$app->user->login($identity))->true();
    }
    
    public function testPasswordHistoryPolicy()
    {
        // Asure that everything is configured properly
        verify('Check that the advanced directory exists', is_dir(Commons::ADVANCED_MIGRATIONS_DIR))->true();

        $files = scandir(Commons::ADVANCED_MIGRATIONS_DIR);
        $result = preg_grep('/'. self::ATTR_PASSWORD_HISTORY .'/', $files);
        verify('Check that the migration exists', $result)->notEmpty();

        verify('Check that the table is added to the database (the migration is run)',
            Yii::$app->db->schema->getTableSchema('password_history'))->notNull();

        verify('Asure that the model is created', Yii::createObject(self::PASSWORD_HISTORY_MODEL))->notNull();

        // Behavior validations
        $changePasswordForm = Yii::createObject(Yii::$app->user->changePasswordForm);
        
        $behavior = $changePasswordForm->attachBehavior('passwordHistoryPolicy', 'nkostadinov\user\behaviors\PasswordHistoryPolicyBehavior');
        verify('Check that the behavior exists', $behavior)->notNull();
        verify('Check that lastPasswordChangesCount field exists', isset($behavior->lastPasswordChangesCount))->true();
        verify('Check that the default value of lastPasswordChangesCount is set to 5',
            $behavior->lastPasswordChangesCount)->equals(5);

        // Try to change the password for a first time by adding the same password
        Commons::createUser();
        $changePasswordForm->email = Commons::TEST_EMAIL;
        $changePasswordForm->oldPassword = 'test123';
        $changePasswordForm->newPassword = 'test123';
        $changePasswordForm->newPasswordRepeat = 'test123';

        verify('Assure that the password cannot be changed, because it is the same as the previous one', $changePasswordForm->changePassword())->false();
        verify('Assure that exactly the new password field has errors', $changePasswordForm->hasErrors('newPassword'))->true();
        verify('Assure that the error on the new password is the error we expect',
            $changePasswordForm->getErrors('newPassword')[0])->equals(PasswordHistoryPolicyBehavior::MESSAGE_SAME_PASSWORDS);

        // Change the password this time for real
        $userId = $changePasswordForm->getUser()->id;        
        $changePasswordForm->newPassword = 'BabaGusi';
        $changePasswordForm->newPasswordRepeat = 'BabaGusi';
        verify('Assure that the password is successfuly changed', $changePasswordForm->changePassword())->true();

        $previousPasswords = PasswordHistory::findAllByUserId($userId);
        verify('Assure that both - the first and the new passwords are added to the history table',
            count($previousPasswords))->equals(2);

        // Try to change the password by adding a password that has already been used in the past
        $changePasswordForm->oldPassword = 'BabaGusi';
        $changePasswordForm->newPassword = 'test123';
        $changePasswordForm->newPasswordRepeat = 'test123';

        verify('Assure that the password cannot be changed, because it is the same as a password added in the past',
            $changePasswordForm->changePassword())->false();
        verify('Assure that exactly the new password field has errors', $changePasswordForm->hasErrors('newPassword'))->true();
        verify('Assure that the error on the new password is the error we expect',
            $changePasswordForm->getErrors('newPassword')[0])->equals(PasswordHistoryPolicyBehavior::MESSAGE_SAME_PREV_PASSWORDS);

        // Change the password for a second time (this time for real)
        $changePasswordForm->oldPassword = 'BabaGusi';
        $changePasswordForm->newPassword = 'AllahuAkbar';
        $changePasswordForm->newPasswordRepeat = 'AllahuAkbar';
        verify('Assure that the password is successfuly changed', $changePasswordForm->changePassword())->true();

        $previousPasswords = PasswordHistory::findAllByUserId($userId);
        verify('Assure that the new password is added to the history table', count($previousPasswords))->equals(3);
    }

    public function testLockOutPolicy()
    {
        // Asure that everything is configured properly
        verify('Check that the advanced directory exists', is_dir(Commons::ADVANCED_MIGRATIONS_DIR))->true();

        $files = scandir(Commons::ADVANCED_MIGRATIONS_DIR);
        $result = preg_grep('/lock_out_policy/', $files);
        verify('Check that the migration exists', $result)->notEmpty();

        $user = new User();
        verify('Check that the login_attempts field is added to the user\'s table',
            $user->hasAttribute(self::ATTR_LOGIN_ATTEMPTS))->true();
        verify('Check that the locked_until field is added to the user\'s table',
            $user->hasAttribute(self::ATTR_LOCKED_UNTIL))->true();

        // Behavior validations
        $loginForm = Yii::createObject(Yii::$app->user->loginForm);
        $loginForm->username = Commons::TEST_EMAIL;
        
        $behavior = $loginForm->attachBehavior('unsuccessfulLoginAttempts', 'nkostadinov\user\behaviors\UnsuccessfulLoginAttemptsBehavior');
        verify('Check that the behavior exists', $behavior)->notNull();
        verify('Check that maxLoginAttempts field exists', isset($behavior->maxLoginAttempts))->true();
        verify('Check that the default value of maxLoginAttempts is set to 5',
            $behavior->maxLoginAttempts)->equals(5);

        verify('Check that lockExpiration field exists', isset($behavior->lockExpiration))->true();
        verify('Check that the default value of lockExpiration is set to 1 hour (in seconds)',
            $behavior->lockExpiration)->equals(3600);

        $user = Commons::createUser();

        // Create one user and check the default values
        verify('Asure that the login_attempts field is empty', $user->login_attempts)->equals(0);
        verify('Asure that the locked_until field is empty', $user->locked_until)->null();

        // Try to login with wrong password
        $loginForm->password = 'ghfghfhsdf';
        $loginForm->login();

        $user->refresh();
        verify('Check that the login attemps field is initialized', $user->login_attempts)->equals(1);

        $this->specify('Lock the account', function() use ($loginForm, $user) {
            $behavior = $loginForm->getBehavior('unsuccessfulLoginAttempts');
            for ($i = 1; $i < $behavior->maxLoginAttempts; $i++) { // Start from 1 because we already have one attempt
                $loginForm->login();
            }
        }, ['throws' => new ForbiddenHttpException()]);

        // Check the lock values
        $behavior = $loginForm->getBehavior('unsuccessfulLoginAttempts');
        $user->refresh();

        verify('Check that the login_attemps field is properly set', $user->login_attempts)->equals($behavior->maxLoginAttempts);
        verify('Check that the locked_until field is set', $user->locked_until)->notNull();
        verify('Check that the locked_until field is set in the future', $user->locked_until)->greaterThan(time());

        // Login the account after the lock ends
        // Simulate that the lock ends
        $user->locked_until = strtotime('-2 weeks');
        $user->save(false);

        $loginForm->password = 'test123';
        verify('Check that the login is successful', $loginForm->login())->true();

        $user->refresh();
        verify('Check that the login_attempts field is set to 0', $user->login_attempts)->equals(0);
        verify('Check that the locked_until field is null', $user->locked_until)->null();

        // Try to login again with unsuccessful password to check the updated values after the clean up
        $loginForm->password = 'AzObi4amMa4IBoza';
        verify('Check that the login is unsuccessful', $loginForm->login())->false();

        $user->refresh();
        verify('Check that the login_attempts field is 1', $user->login_attempts)->equals(1);
        verify('Check that the locked_until field is still null', $user->locked_until)->null();

        // Login and check the defaults, in order to prove that only consequent attempts are being counted
        $loginForm->password = 'test123';
        verify('Check that the login is successful', $loginForm->login())->true();

        $user->refresh();
        verify('Check that the login_attempts field is set to 0', $user->login_attempts)->equals(0);
        verify('Check that the locked_until field is still null', $user->locked_until)->null();
    }

    public function testChangePasswordAfterFirstLogin()
    {
        // Asure that everything is configured properly
        verify('Check that the advanced directory exists', is_dir(Commons::ADVANCED_MIGRATIONS_DIR))->true();

        $files = scandir(Commons::ADVANCED_MIGRATIONS_DIR);
        $result = preg_grep('/'. self::ATTR_REQUIRE_PASSWORD_CHANGE .'/', $files);
        verify('Check that the migration exists', $result)->notEmpty();

        verify('Check that the field is added to the table (the migration is run)',
            (new User())->hasAttribute(self::ATTR_REQUIRE_PASSWORD_CHANGE))->true();

        // Behavior validations
        $behavior = Yii::$app->user->attachBehavior('firstLoginPolicy', 'nkostadinov\user\behaviors\FirstLoginPolicyBehavior');
        verify('Check that the behavior exists', $behavior)->notNull();

        $user = Commons::createUser();

        // Defaults validations
        verify('Check that the default value of the ' . self::ATTR_REQUIRE_PASSWORD_CHANGE . ' field is set to 1',
            $user->require_password_change)->equals(1);

        // The user is required to change his password on a first login
        verify('Check that the login fails', Yii::$app->user->login($user))->false();

        // Change the password of the user and check the user is logged in
        $changePasswordForm = new ChangePasswordForm();
        $changePasswordForm->email = Commons::TEST_EMAIL;
        $changePasswordForm->oldPassword = 'test123';
        $changePasswordForm->newPassword = 'Risto-Bageristo';
        $changePasswordForm->newPasswordRepeat = 'Risto-Bageristo';
        $changePasswordForm->changePassword(); // The user is logged in after a password change

        $user->refresh();
        verify('Asure the ' . self::ATTR_REQUIRE_PASSWORD_CHANGE . ' is set to 0', $user->require_password_change)->equals(0);
        verify('Check that the login passes', Yii::$app->user->isGuest)->false();

        Yii::$app->user->logout(); // Logout the user to continue testing without a logged in user
    }
}
