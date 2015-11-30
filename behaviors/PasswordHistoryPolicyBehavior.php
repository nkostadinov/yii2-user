<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\models\PasswordHistory;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * Prevents users from creating a password that has already been used in the past.
 * This adds more security to the application.
 */
class PasswordHistoryPolicyBehavior extends Behavior
{
    /**
     * @var integer The number of the password changes, that the system will check.
     */
    public $lastPasswordChangesCount = 5;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'initPasswordHistory',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'allowPasswordChange',
        ];
    }

    /**
     * 
     * @param AfterSaveEvent $afterSaveEvent
     */
    public function initPasswordHistory(AfterSaveEvent $afterSaveEvent)
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->user_id = $afterSaveEvent->sender->id;
        $passwordHistory->password_hash = $afterSaveEvent->sender->password_hash;
        $passwordHistory->save();
    }

    public function allowPasswordChange(ModelEvent $modelEvent)
    {

    }
}
