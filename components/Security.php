<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.03.2015
 * Time: 16:45 ч.
 */

namespace nkostadinov\user\components;


use nkostadinov\user\interfaces\IUserSecurity;

class Security implements IUserSecurity {

    public function hasAccess($resource, $params = [])
    {
        switch($resource) {
            case 'user.admin':
                return !\Yii::$app->user->isGuest;
            default:
                return true;
        }
    }

    public function hasRole($role, $params = [])
    {
        // TODO: Implement hasRole() method.
    }
}