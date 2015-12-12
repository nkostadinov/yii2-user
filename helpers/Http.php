<?php

namespace nkostadinov\user\helpers;

use Yii;

/**
 * HTTP-related helpers.
 *
 * @author Nikolay Traykov
 */
class Http
{
    /**
     * @return string The user IP or a sample string if this is a console application.
     */
    public static function getUserIP()
    {
        return Yii::$app->getRequest()->isConsoleRequest ? '(console)' : Yii::$app->getRequest()->getUserIP();
    }
}
