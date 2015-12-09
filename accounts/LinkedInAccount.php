<?php

namespace nkostadinov\user\accounts;

use nkostadinov\user\interfaces\IUserAccount;
use yii\authclient\clients\LinkedIn;

class LinkedInAccount extends LinkedIn implements IUserAccount
{
    /** @return integer User's id */
    public function getUserId()
    {
        return $this->getAttributeValue('id');
    }

    /** @return string|null User's email */
    public function getEmail()
    {
        return $this->getAttributeValue('email-address');
    }

    /** @return string|null User's username */
    public function getUsername()
    {
        return null;
    }

    /** @return string|null User's name */
    public function getRealName()
    {
        return $this->getAttributeValue('first-name') . ' ' . $this->getAttributeValue('last-name');
    }

    private function getAttributeValue($attributeName)
    {
        if (isset($this->getUserAttributes()[$attributeName])) {
            return $this->getUserAttributes()[$attributeName];
        }

        return null;
    }
}
