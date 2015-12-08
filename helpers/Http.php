<?php

namespace nkostadinov\user\helpers;

use Yii;

/**
 * HTTP related helpers.
 *
 * @author Nikolay Traykov
 */
class Http
{
    public static function getUserIP()
    {
        return Yii::$app->getRequest()->isConsoleRequest ? '(console)' : Yii::$app->getRequest()->getUserIP();
    }
}