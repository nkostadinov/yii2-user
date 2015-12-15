<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 29.03.2015
 * Time: 22:52 ч.
 */

namespace nkostadinov\user\models\forms;

use nkostadinov\user\models\Token;
use nkostadinov\user\Module;
use Yii;
use yii\base\Model;

class RecoveryForm extends Model
{
    public $email;
    public $user;

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => Yii::$app->user->identityClass,
                'message' => Yii::t(Module::I18N_CATEGORY, 'There is no user with this email address')
            ],
            ['email', function ($attribute) {
                $this->user = Yii::$app->user->findUserByEmail($this->email);
                if ($this->user !== null && Yii::$app->user->enableConfirmation && !$this->user->getIsConfirmed()) {
                    $this->addError($attribute, Yii::t(Module::I18N_CATEGORY, 'You need to confirm your email address'));
                }
            }],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t(Module::I18N_CATEGORY, 'Email'),
        ];
    }

    /**
     * Sends recovery message.
     *
     * @return bool
     */
    public function sendRecoveryMessage()
    {
        if ($this->validate()) {
            /** @var Token $token */
            $token = Yii::createObject([
                'class'   => Token::className(),
                'user_id' => $this->user->id,
                'type'    => Token::TYPE_RECOVERY
            ]);
            $token->save(false);

            Yii::$app->user->notificator->sendRecoveryMessage($this->user, $token);
            return true;
        }
        return false;
    }
}
