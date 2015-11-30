<?php

use nkostadinov\user\models\User;

/**
 * Description of Commons
 *
 * @author ntraykov
 */
class Commons
{
    const TEST_EMAIL = 'test@innologica.com';

    const ADVANCED_MIGRATIONS_DIR = __DIR__ . '/../../migrations/advanced';

    public static function createUser($email, $password, $status = 1)
    {
        $user = new User();
        $user->email = $email;
        $user->status = $status;
        $user->confirmed_on = time();
        $user->setPassword($password);
        $user->save(false);

        return $user;
    }
}
