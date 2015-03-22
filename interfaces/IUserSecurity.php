<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.03.2015
 * Time: 08:52 ч.
 */

namespace nkostadinov\user\interfaces;

interface IUserSecurity {
    public function hasAccess($resource, $params = []);
    public function hasRole($role, $params = []);
}