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
use yii\swiftmailer\Mailer;

class MailNotificator extends Component implements IUserNotificator {

    public $mailer = 'mailer';
    public $mailSubject = 'Registration confirmation';
    public $sender = 'yii2-user@example.com';

    public function sendRecoveryMessage($user, $token)
    {
        // TODO: Implement sendRecoveryMessage() method.
    }

    public function sendConfirmationMessage($user, $token)
    {
        $this->getMailer()->compose()
            ->setFrom($this->sender)
            ->setTo($user->email)
            ->setSubject($this->mailSubject)
            ->setTextBody($token->code)
            ->setHtmlBody("<b>{$token->code}</b>")
            ->send();
    }

    /**
     * @return Mailer
     */
    public function getMailer()
    {
        return Yii::$app->{$this->mailer};
    }
}