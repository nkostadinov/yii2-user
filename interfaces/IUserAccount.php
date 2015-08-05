<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.04.2015
 * Time: 09:52 ч.
 */

namespace nkostadinov\user\interfaces;


use yii\authclient\ClientInterface;

interface IUserAccount extends ClientInterface
{
    /** @return string|null User's email */
    public function getEmail();
    /** @return string|null User's username */
    public function getUsername();
    /** @return string|null User's name */
    public function getRealName();
}