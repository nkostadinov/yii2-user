<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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