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
    const USER_ADMINISTRATION_EDIT = 'user.admin.update';

    public function hasAccess($resource, $params = [])
    {
        switch($resource) {
            case self::USER_ADMINISTRATION:
            //case self::USER_ADMINISTRATION_EDIT:
                return !\Yii::$app->user->isGuest;
            default:
                return true;
        }
    }

}