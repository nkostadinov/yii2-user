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
    const SCENARIO_RECOVERY = 'recovery';

    public $email;
    public $user;

    public function init()
    {
        $this->scenario = self::SCENARIO_RECOVERY;
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', function($attribute) {
                $this->user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'], ['email' => $this->email]);
                if (!$this->user) {
                    $this->addError($attribute, Yii::t(Module::I18N_CATEGORY, 'There is no user with this email address'));
                }
            }],
            ['email', function ($attribute) {
                if (Yii::$app->user->enableConfirmation && !$this->user->getIsConfirmed()) {
                    $this->addError($attribute, Yii::t(Module::I18N_CATEGORY, 'You need to confirm your email address'));
                }
            }, 'on' => self::SCENARIO_RECOVERY],
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
