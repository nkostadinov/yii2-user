<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.04.2015
 * Time: 09:59 ч.
 */

namespace nkostadinov\user\accounts;


use nkostadinov\user\interfaces\IUserAccount;
use yii\authclient\clients\Facebook;

class FacebookAccount extends Facebook implements IUserAccount{

    /** @return string|null User's email */
    public function getEmail()
    {
        // TODO: Implement getEmail() method.
    }

    /** @return string|null User's username */
    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    /** @return string|null User's name */
    public function getRealName()
    {
        // TODO: Implement getRealName() method.
    }
}