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

    protected function _before()
    {
    }

    protected function _after()
    {
        User::deleteAll('email = :email', [':email' => Commons::TEST_EMAIL]);
    }

    // tests
    public function testPasswordAging()
    {
        $this->specify('test', function() {
            verify('check whether the advanced directory exists', is_dir(Commons::ADVANCED_MIGRATIONS_DIR))->true();
        });
        
//
//        // check that the migration exists
//        $files = scandir(self::ADVANCED_MIGRATIONS_DIR);
//        $result = preg_grep('/'. self::ATTR_PASSWORD_CHANGED_AT .'/', $files);
//        $I->assertNotEmpty($result);
//
//        // check that the field is added to the table /e.g. the migration is run/
//        $I->assertTrue((new User())->hasAttribute(self::ATTR_PASSWORD_CHANGED_AT));

        // check that the behavior exists
    }
}