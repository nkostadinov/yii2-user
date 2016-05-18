<?php
namespace Codeception\Module;

use nkostadinov\user\models\Token;
use nkostadinov\user\models\User;

define('ADVANCED_DIR_PATH', __DIR__ . '/../../migrations/advanced');

/**
 * Common functionalities for all kinds of tests.
 *
 * @author ntraykov
 */
class Commons
{
    const TEST_EMAIL = 'test@innologica.com';
    const TEST_USERNAME = 'innologica';
    const TEST_PASSWORD = 'Ni$test123';

    const ADVANCED_MIGRATIONS_DIR = ADVANCED_DIR_PATH;

    /**
     * Creates a new, confirmed user.
     *
     * @param type $email
     * @param type $password
     * @param type $status Whether the password is active or not. Defaults to 'active'.
     * @return User
     */
    public static function createUser($email = self::TEST_EMAIL, $password = self::TEST_PASSWORD, $status = User::STATUS_ACTIVE)
    {
        $user = new User();
        $user->email = $email;
        $user->status = $status;
        $user->confirmed_on = time();
        $user->setPassword($password);
        $user->save(false);
        
        $user->refresh(); // To load the defaults set by the database
        
        return $user;
    }

    public static function createUnconfirmedUser($email = self::TEST_EMAIL, $password = self::TEST_PASSWORD)
    {
        $user = new User();
        $user->email = $email;
        $user->setPassword($password);
        $user->save(false);

        return $user;
    }

    public static function createTokenForUser($userId, $type = Token::TYPE_RECOVERY)
    {
        $token = Yii::createObject([
            'class'   => Token::className(),
            'user_id' => $userId,
            'type'    => $type
        ]);
        $token->save(false);

        return $token;
    }

    public static function createUserWithUsername($email = self::TEST_EMAIL, $password = self::TEST_PASSWORD, $username = self::TEST_USERNAME)
    {
        $user = new User();
        $user->email = $email;
        $user->username = $username;
        $user->status = 1;
        $user->confirmed_on = time();
        $user->setPassword($password);
        $user->save(false);

        $user->refresh(); // To load the defaults set by the database

        return $user;
    }
}
