<?php

namespace nkostadinov\user\events;

use yii\base\Event;

/**
 * Triggered in third-party authentications.
 *
 * @author Nikolay Traykov
 */
class AuthEvent extends Event
{
    public $account;
    public $client;
}