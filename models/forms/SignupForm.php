<?php

namespace nkostadinov\user\models\forms;

use nkostadinov\user\helpers\Http;
use nkostadinov\user\models\User;
use nkostadinov\user\Module;
use nkostadinov\user\validators\PasswordStrengthValidator;
use Yii;
use yii\base\Model;

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
        $rules = [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'uniqueEmail'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->user->minPasswordLength],
            array_merge(['password', PasswordStrengthValidator::className()],
                Yii::$app->user->passwordStrengthConfig),
        ];

        if(\Yii::$app->user->requireUsername === true) {
            $rules[] = ['username', 'required'];
            $rules[] =  ['username', 'string', 'min' => 2, 'max' => 255];
            $rules[] =  ['username', 'filter', 'filter' => 'trim'];
            //['username', 'unique', 'targetClass' => 'nkostadinov\user\models\User', 'message' => 'This username has already been taken.'],
        }

        return $rules;
    }

    public function uniqueEmail($attribute)
    {

        $user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'],
            ['email' => $this->$attribute]);
        if ($user && $user->password_hash) {
            $this->addError($attribute, Yii::t(Module::I18N_CATEGORY, 'This email address has already been taken.'));
        }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'],
                ['email' => $this->email]);
            if (!$user) {
                $user = Yii::createObject([
                    'class' => Yii::$app->user->identityClass,
                ]);
                $user->email = $this->email;
                $user->register_ip = Http::getUserIP();
            }
            $user->setPassword($this->password);

            return Yii::$app->user->register($user);
        }

        return false;
    }
}
