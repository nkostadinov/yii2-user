<?php

namespace nkostadinov\user\models\forms;

use nkostadinov\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Covers three cases:
 *  - Change password - when the user wants a password change by his/hers desire;
 *  - Required password change - when the system requires from the user a password change.
 *    In this case the user is not logged in and the 'email' and the 'oldPassword' fields are needed.
 *  - Reset password - after reseting password the user is sent to change it immedeately.
 */
class ChangePasswordForm extends Model
{
    const SCENARIO_REQUIRE_PASSWORD_CHANGE = 'requirePasswordChange';

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
            ['email', 'filter', 'filter' => 'trim'],

            [['newPassword', 'newPasswordRepeat'], 'string', 'min' => Yii::$app->user->minPasswordLength],
            [['newPassword', 'newPasswordRepeat'], 'required'],
            [['newPassword', 'newPasswordRepeat'], 'validateNewPasswords'],

            ['email', 'required', 'on' => self::SCENARIO_REQUIRE_PASSWORD_CHANGE],
            ['oldPassword', 'required', 'on' => self::SCENARIO_REQUIRE_PASSWORD_CHANGE],
            ['oldPassword', 'validatePassword', 'on' => self::SCENARIO_REQUIRE_PASSWORD_CHANGE],
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

            if ($user->hasAttribute('password_changed_at'))
                $user->password_changed_at = time();

            if (!empty($user->require_password_change))
                $user->require_password_change = 0;

            if ($user->save()) {
                if (Yii::$app->user->isGuest) {
                    return Yii::$app->user->login($user);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        if (!$this->_user) {
            if (!Yii::$app->user->isGuest) {
                $this->_user = Yii::$app->user->identity;
            } else {
                $this->_user = User::findByEmail($this->email);
            }
        }

        return $this->_user;
    }

    /**
     * A central point for determining the scenario.
     *
     * If the user is not logged in, the scenario is ChangePasswordForm::SCENARIO_REQUIRE_PASSWORD_CHANGE.
     * In all other cases the user is logged in, so no further check is needed so far.
     */
    public function scenario()
    {
        if (Yii::$app->user->isGuest) {
            $this->scenario = self::SCENARIO_REQUIRE_PASSWORD_CHANGE;
        }
    }
}
