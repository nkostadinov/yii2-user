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
        Yii::info("User is trying to register", __CLASS__);
        if ($this->validate()) {
            Yii::info("User [$this->email] passed the registration validation", __CLASS__);
            $user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'],
                ['email' => $this->email]);
            if (!$user) {
                /** @var User $user */
                $user = Yii::createObject([
                    'class' => Yii::$app->user->identityClass,
                ]);
                //assign all safe attributes from the form input to the user model
                $user->setAttributes($this->getAttributes());
            }
            $user->setPassword($this->password);

            return Yii::$app->user->register($user);
        }

        return false;
    }
}
