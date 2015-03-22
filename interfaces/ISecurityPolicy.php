<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.03.2015
 * Time: 08:52 ч.
 */

namespace nkostadinov\user\interfaces;

interface ISecurityPolicy {
    public function hasAccess($resource, $params = []);
}