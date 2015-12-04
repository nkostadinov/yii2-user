<?php

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
    const TEST_PASSWORD = 'test123';

    const ADVANCED_MIGRATIONS_DIR = ADVANCED_DIR_PATH;

    /**
     * Creates a new, confirmed user.
     *
     * @param type $email
     * @param type $password
     * @param type $status Whether the password is active or not. Defaults to 'active'.
     * @return nkostadinov\user\models\User
     */
    public static function createUser($email = self::TEST_EMAIL, $password = self::TEST_PASSWORD, $status = 1)
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
}
