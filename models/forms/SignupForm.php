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
    public $name;
    public $username;
    public $email;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => 'nkostadinov\user\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->user->minPasswordLength],

            ['name', 'string', 'min' => 3],
        ];

        if(\Yii::$app->user->requireUsername === true) {
            $rules[] = ['username', 'required'];
            $rules[] =  ['username', 'string', 'min' => 2, 'max' => 255];
            $rules[] =  ['username', 'filter', 'filter' => 'trim'];
            //['username', 'unique', 'targetClass' => 'nkostadinov\user\models\User', 'message' => 'This username has already been taken.'],
        }

        return $rules;
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = Yii::createObject([
                'class' => Yii::$app->user->identityClass,
                'scenario' => 'register',
            ]);
            $user->attributes = $this->attributes;
            $user->setPassword($this->password);

            return Yii::$app->user->register($user);
        }

        return false;
    }
}
