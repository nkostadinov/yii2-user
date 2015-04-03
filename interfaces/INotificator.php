<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 02.04.2015
 * Time: 21:05 ч.
 */

namespace nkostadinov\user\interfaces;


interface IUserNotificator {
    public function sendRecoveryMessage($user, $token);
}