<?php
namespace nkostadinov\user\accounts;

use nkostadinov\user\interfaces\IUserAccount;
use yii\authclient\clients\Twitter;

class TwitterAccount extends Twitter implements IUserAccount
{
    /** @return integer User's id */
    public function getUserId()
    {
        return strval($this->getAttributeValue('id'));
    }

    /** @return string|null User's email */
    public function getEmail()
    {
        return $this->getAttributeValue('email');
    }

    /** @return string|null User's username */
    public function getUsername()
    {
        return $this->getAttributeValue('screen_name');
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
