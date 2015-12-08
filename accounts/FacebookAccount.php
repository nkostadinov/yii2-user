<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.04.2015
 * Time: 09:59 ч.
 */

namespace nkostadinov\user\accounts;


use nkostadinov\user\interfaces\IUserAccount;
use yii\authclient\clients\Facebook;

class FacebookAccount extends Facebook implements IUserAccount
{
    /** @return integer User's id */
    public function getUserId()
    {
        return $this->getAttributeValue('id');
    }

    /** @return string|null User's email */
    public function getEmail()
    {
        return $this->getAttributeValue('email');
    }

    /** @return string|null User's username */
    public function getUsername()
    {
        return $this->getAttributeValue('username');
    }

    /** @return string|null User's name */
    public function getRealName()
    {
        return $this->getAttributeValue('name');
    }

    private function getAttributeValue($attributeName)
    {
        if (isset($this->getUserAttributes()[$attributeName])) {
            return $this->getUserAttributes()[$attributeName];
        }

        return null;
    }
}
