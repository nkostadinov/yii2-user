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
    public $password;

    public $user;

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t(Module::I18N_CATEGORY, 'Email'),
            'password' => Yii::t(Module::I18N_CATEGORY, 'Password'),
        ];
    }

    /** @inheritdoc */
    public function scenarios()
    {
        return [
            'request' => ['email'],
            'reset'   => ['password']
        ];
    }


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
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
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
            Yii::$app->session->setFlash('info', Yii::t(Module::I18N_CATEGORY, 'An email has been sent with instructions for resetting your password'));
            return true;
        }
        return false;
    }
}
