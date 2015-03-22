<?php

namespace nkostadinov\user\models\forms;

use app\models\UserAccount;
use nkostadinov\user\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $time_zone;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => 'nkostadinov\user\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => 'nkostadinov\user\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['time_zone', 'required']
        ];
    }

    /**
     * Signs user up.
     *
     * @param UserAccount $account linked account from which comes the registration
     * @return User|null the saved model or null if saving fails
     */
    public function signup($account = null)
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->time_zone = $this->time_zone;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if(isset($account))
                $user->register_provider_id = $account->user_provider_id;
            $user->save();

            //update account's user id
            if(isset($account)) {
                $account->user_id = $user->id;
                $account->save();
            }

            return $user;
        }

        return null;
    }
}
