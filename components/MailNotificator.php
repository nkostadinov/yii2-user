<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 02.04.2015
 * Time: 21:08 ч.
 */

namespace nkostadinov\user\components;


use nkostadinov\user\interfaces\IUserNotificator;

class MailNotificator implements IUserNotificator {

    public function sendRecoveryMessage($user, $token)
    {
        // TODO: Implement sendRecoveryMessage() method.
    }
}