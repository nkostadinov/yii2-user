<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 25.03.2015
 * Time: 13:56 ч.
 */

namespace nkostadinov\user\components;


use nkostadinov\user\models\User as UserModel;
use nkostadinov\user\models\UserSearch;
use yii\web\User as BaseUser;

class User extends BaseUser
{
    public $identityClass = 'nkostadinov\user\models\User';

    public $loginForm = 'nkostadinov\user\models\forms\LoginForm';
    public $registerForm = 'nkostadinov\user\models\forms\SignupForm';
    public $recoveryForm = 'nkostadinov\user\models\forms\RecoveryForm';

    public $enableConfirmation = true;

    public function listUsers($params)
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($params);
        return $dataProvider;
    }

    public function findUserByEmail($email)
    {
        return UserModel::findOne([
            'email' => $email,
        ]);
    }
}