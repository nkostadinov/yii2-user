<?php

namespace nkostadinov\user\models\forms;

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
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = Yii::createObject(Yii::$app->user->identityClass);
            $user->attributes = $this->attributes;
            $user->setPassword($this->password);
            //TODO: add token genration for confirmation
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
