<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 20.04.2015
 * Time: 14:20 ч.
 */

namespace nkostadinov\user\behaviors;


use nkostadinov\user\components\User;
use nkostadinov\user\events\UserRegisterEvent;
use yii\base\Behavior;
use yii\helpers\VarDumper;
use yii\web\UserEvent;

class LastLoginBehavior extends Behavior
{
    public function events()
    {
        return [
            User::EVENT_AFTER_LOGIN => [ $this, 'afterLogin'],
        ];
    }

    public function afterLogin(UserEvent $event)
    {
        $event->identity->last_login = time();
        $event->identity->last_login_ip = \Yii::$app->getRequest()->isConsoleRequest ? '(console)' : \Yii::$app->getRequest()->getUserIP();
        $event->identity->save();
    }


}