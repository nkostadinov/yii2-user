<?php
namespace nkostadinov\user\accounts;

use nkostadinov\user\interfaces\IUserAccount;
use yii\authclient\clients\GoogleOAuth;

class GoogleAccount extends GoogleOAuth implements IUserAccount
{
    /** @return integer User's id */
    public function getUserId()
    {
        return $this->getAttributeValue('id');
    }

    /** @return string|null User's email */
    public function getEmail()
    {
        if (isset($this->getUserAttributes()['emails'])) {
            $emails = $this->getUserAttributes()['emails'];
            if (!empty($emails)) {
                return $emails[0]['value'];
            }
        }

        return null;
    }

    /** @return string|null User's username */
    public function getUsername()
    {
        return $this->getAttributeValue('nickname');
    }

    /** @return string|null User's name */
    public function getRealName()
    {
        return $this->getAttributeValue('displayName');
    }

    private function getAttributeValue($attributeName)
    {
        if (isset($this->getUserAttributes()[$attributeName])) {
            return $this->getUserAttributes()[$attributeName];
        }

        return null;
    }
}
