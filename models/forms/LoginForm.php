<?php

namespace nkostadinov\user\models\forms;

use nkostadinov\user\models\User;
use nkostadinov\user\Module;
use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = null;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->password_hash || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t(Module::I18N_CATEGORY, 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        Yii::info('User is trying to login', __CLASS__);
        if ($this->validate()) {
            if(!$this->user->isConfirmed and !Yii::$app->user->allowUncofirmedLogin) {
                Yii::info("User [$this->username] is not confirmed", __CLASS__);
                throw new ForbiddenHttpException(Yii::t(Module::I18N_CATEGORY, 'Unconfirmed account are not allowed to login'));
            }
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        Yii::info("User [$this->username] is unable to login, because of invalid data", __CLASS__);
        
        return false;
    }

    /**
     * Finds user by [[email]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->_user) {
            try {
                $this->_user = call_user_func([Yii::$app->user->identityClass, 'findByEmailOrUsername'],
                    ['value' => $this->username]);
            } catch (NotFoundHttpException $ex) {}
        }

        return $this->_user;
    }
}
