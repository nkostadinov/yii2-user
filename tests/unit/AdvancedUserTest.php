<?php

use nkostadinov\user\models\User;
use yii\codeception\TestCase;

class AdvancedUserTest extends TestCase
{   
    use \Codeception\Specify;

    const ATTR_PASSWORD_CHANGED_AT = 'password_changed_at';

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
        $this->specify('Asure that everything is configured properly', function() {
            verify('Check that the advanced directory exists', is_dir(Commons::ADVANCED_MIGRATIONS_DIR))->true();

            $files = scandir(Commons::ADVANCED_MIGRATIONS_DIR);
            $result = preg_grep('/'. self::ATTR_PASSWORD_CHANGED_AT .'/', $files);
            verify('Check that the migration exists', $result)->notEmpty();

            verify('Check that the field is added to the table (the migration is run)',
                (new User())->hasAttribute(self::ATTR_PASSWORD_CHANGED_AT))->true();
        });

        $this->specify('Behavior validations', function() {
            $behavior = Yii::$app->user->attachBehavior('passwordAgingBehavior', 'nkostadinov\user\behaviors\PasswordAgingBehavior');
            verify('Check that the behavior exists', $behavior)->notNull();

            verify('Check that passwordChangeInterval field exists', $behavior->passwordChangeInterval);
            verify('Check that the default value of passwordChangeInterval is set to two months (in seconds)',
                $behavior->passwordChangeInterval)->equals(60 * 60 * 24 * 30 * 2);
        });
        
        $identity = Commons::createUser(Commons::TEST_EMAIL, 'test');
        $this->specify('Create one user and set it\'s default password_changed_at value to older than two months', function() use ($identity) {
            $identity->setAttribute(self::ATTR_PASSWORD_CHANGED_AT, strtotime('-3 months'));
            $identity->save('false');
            verify('The login is unsuccessful', Yii::$app->user->login($identity))->false();
        });

        $this->specify('Now set the value of password_changed_at field to 1 month', function() use ($identity) {
            $identity->setAttribute(self::ATTR_PASSWORD_CHANGED_AT, strtotime('-1 month'));
            $identity->save('false');
            verify('The login is successful', Yii::$app->user->login($identity))->true();
        });
    }
}