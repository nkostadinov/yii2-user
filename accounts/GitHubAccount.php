<?php

namespace nkostadinov\user\accounts;

use nkostadinov\user\interfaces\IUserAccount;
use yii\authclient\clients\GitHub;

class GitHubAccount extends GitHub implements IUserAccount
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
        return $this->getAttributeValue('login');
    }

    /** @return string|null User's name */
    public function getRealName()
    {
        return null;
    }

    private function getAttributeValue($attributeName)
    {
        if (isset($this->getUserAttributes()[$attributeName])) {
            return $this->getUserAttributes()[$attributeName];
        }

        return null;
    }
}
