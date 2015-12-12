<?php

namespace nkostadinov\user\helpers;

use nkostadinov\user\events\AuthEvent;
use nkostadinov\user\events\ModelEvent;
use nkostadinov\user\interfaces\IUserAccount;
use nkostadinov\user\models\UserAccount;
use Yii;
use yii\web\IdentityInterface;
use yii\web\UserEvent;

/**
 * Helper for an event-related tasks.
 *
 * @author Nikolay Traykov
 */
class Event
{
    /**
     * @param type $model
     * @return ModelEvent
     */
    public static function createModelEvent($model)
    {
        return Yii::createObject(['class' => ModelEvent::className(), 'model' => $model]);
    }

    /**
     * @param IdentityInterface $identity
     * @return UserEvent
     */
    public static function createUserEvent(IdentityInterface $identity)
    {
        return Yii::createObject(['class' => UserEvent::className(), 'identity' => $identity]);
    }

    /**
     * @param UserAccount $account
     * @param IUserAccount $client
     * @return AuthEvent
     */
    public static function createAuthEvent(UserAccount $account, IUserAccount $client)
    {
        return Yii::createObject([
            'class' => AuthEvent::className(),
            'account' => $account,
            'client' => $client,
        ]);
    }
}
