<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 20.04.2015
 * Time: 14:20 ч.
 */

namespace nkostadinov\user\behaviors;

use nkostadinov\user\components\User;
use Yii;
use yii\base\Behavior;
use yii\web\UserEvent;

class LastLoginBehavior extends Behavior
{
    public function events()
    {
        return [
            User::EVENT_AFTER_LOGIN => 'setLastLogin',
        ];
    }

    public function setLastLogin(UserEvent $event)
    {
        $behavior = Yii::$app->user->identity->getBehavior('timestamp');
        Yii::$app->user->identity->detachBehavior('timestamp');
        $event->identity->last_login = time();
        $event->identity->last_login_ip = Yii::$app->getRequest()->isConsoleRequest ? '(console)' : Yii::$app->getRequest()->getUserIP();
        $event->identity->save();
        Yii::$app->user->attachBehavior('timestamp', $behavior);
    }
}
