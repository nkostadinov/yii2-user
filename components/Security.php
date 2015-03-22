<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.03.2015
 * Time: 16:45 ч.
 */

namespace nkostadinov\user\components;


use nkostadinov\user\interfaces\ISecurityPolicy;

class Security implements ISecurityPolicy
{
    const USER_ADMINISTRATION = 'user.admin';

    public function hasAccess($resource, $params = [])
    {
        switch($resource) {
            case self::USER_ADMINISTRATION:
                return !\Yii::$app->user->isGuest;
            default:
                return true;
        }
    }

}