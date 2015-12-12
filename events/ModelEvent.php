<?php

namespace nkostadinov\user\events;

/**
 * An event used where forms are involved. Can be used with active records as well.
 *
 * @author Nikolay Traykov
 */
class ModelEvent extends \yii\base\ModelEvent
{
    public $model;
}
