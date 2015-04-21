<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 20.04.2015
 * Time: 15:20 ч.
 */

namespace nkostadinov\user\events;


use yii\base\Event;

class BaseEvent extends Event {
    public $model;
    public $isValid;
}