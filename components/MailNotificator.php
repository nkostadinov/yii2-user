<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 02.04.2015
 * Time: 21:08 ч.
 */

namespace nkostadinov\user\components;


use nkostadinov\user\interfaces\IUserNotificator;
use Yii;
use yii\base\Component;

class MailNotificator extends Component implements IUserNotificator {

    public $mailer = 'mailer';
    public $mailSubject = 'Registration confirmation';

    public function sendRecoveryMessage($user, $token)
    {
        // TODO: Implement sendRecoveryMessage() method.
    }

    public function sendConfirmationMessage($user, $token)
    {
        Yii::$app->{$this->mailer}->compose()
            ->setFrom('from@domain.com')
            ->setTo($user->email)
            ->setSubject($this->mailSubject)
            ->setTextBody($token->code)
            ->setHtmlBody("<b>{$token->code}</b>")
            ->send();
    }
}