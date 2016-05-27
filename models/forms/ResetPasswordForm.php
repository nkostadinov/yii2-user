<?php

namespace nkostadinov\user\models\forms;

use nkostadinov\user\Module;
use nkostadinov\user\validators\PasswordStrengthValidator;
use Yii;
use yii\base\Model;

/**
 * Reset password form.
 */
class ResetPasswordForm extends Model
{
    public $newPassword;
    public $newPasswordRepeat;
    public $token;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['newPassword', 'newPasswordRepeat'], 'string', 'min' => Yii::$app->user->minPasswordLength],
            [['newPassword', 'newPasswordRepeat'], 'required'],
            [['newPassword', 'newPasswordRepeat'], 'validateNewPasswords'],
            array_merge(['newPassword', PasswordStrengthValidator::className()],
                Yii::$app->user->passwordStrengthConfig),
        ];
    }

    public function attributeLabels()
    {
        return [
            'newPassword' => Yii::t(Module::I18N_CATEGORY, 'New password'),
            'newPasswordRepeat' => Yii::t(Module::I18N_CATEGORY, 'New password repeat'),
        ];
    }

    public function validateNewPasswords($attribute, $params)
    {
        if ($this->newPassword != $this->newPasswordRepeat) {
            $this->addError($attribute, Yii::t(Module::I18N_CATEGORY, 'Passwords are not the same.'));
        }
    }

    public function reset()
    {
        if ($this->load(Yii::$app->request->post()) && $this->validate()) {
            return Yii::$app->user->resetPassword($this->token, $this->newPassword);
        }
        return false;
    }
}
