<?php

namespace nkostadinov\user\models\forms;

use nkostadinov\user\models\User;
use Yii;
use yii\base\Model;

/**
 * The form used for requiring a password change of a user, that haven't changed
 * his password for PasswordAgingBehavior::$passwordChangeInterval time.
 */
class ChangePasswordForm extends Model
{
    public $email;
    public $oldPassword;
    public $newPassword;
    public $newPasswordRepeat;

    private $_user;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            ['email', 'required'],
            ['email', 'filter', 'filter' => 'trim'],

            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'string', 'min' => Yii::$app->user->minPasswordLength],
            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'required'],

            ['oldPassword', 'validatePassword'],

            [['newPassword', 'newPasswordRepeat'], 'validateNewPasswords']
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'oldPassword' => Yii::t('user', 'Old password'),
            'newPassword' => Yii::t('user', 'New password'),
            'newPasswordRepeat' => Yii::t('user', 'New password repeat'),
        ];
    }

    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->oldPassword)) {
            $this->addError('oldPassword', 'Incorrect email or password.');
            $this->addError('email', 'Incorrect email or password.');
        }
    }

    public function validateNewPasswords($attribute, $params)
    {
        if ($this->newPassword != $this->newPasswordRepeat) {
            $this->addError($attribute, 'The new passwords are not the same.');
        }
    }

    public function changePassword()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $user->setPassword($this->newPassword);
            $user->password_changed_at = time();

            return $user->save() && Yii::$app->user->login($user);
        }

        return false;
    }

    public function getUser()
    {
        if (!$this->_user) {
            $this->_user = User::findByEmail($this->email);
        }

        return $this->_user;
    }
}
