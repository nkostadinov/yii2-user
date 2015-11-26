<?php
namespace Step\Functional;

use FunctionalTester;
use nkostadinov\user\models\User;

class AuthenticationStep extends FunctionalTester
{

    public function register($email, $password)
    {
        $user = new User();
        $user->email = $email;
        $user->password = $password;
        $user->save(false);
    }

    protected function getScenario() 
    {

    }
}