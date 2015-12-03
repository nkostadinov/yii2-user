<?php

use nkostadinov\user\models\User;

/**
 * Common functionalities for all kinds of tests.
 *
 * @author ntraykov
 */
class Commons
{
    const TEST_EMAIL = 'test@innologica.com';

    const ADVANCED_MIGRATIONS_DIR = __DIR__ . '/../../migrations/advanced';

    /**
     * Creates a new, confirmed user.
     *
     * @param type $email
     * @param type $password
     * @param type $status Whether the password is active or not. Defaults to 'active'.
     * @return nkostadinov\user\models\User
     */
    public static function createUser($email, $password, $status = 1)
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
