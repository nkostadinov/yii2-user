<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\Module;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Model;
use yii\web\ForbiddenHttpException;

/**
 * A behavior that locks the user if a wrong password is added in 5 consequent times.
 * The account is locked for the period of Yii::$app->user->lockExpiration (currently one hour by default).
 */
class UnsuccessfulLoginAttemptsBehavior extends Behavior
{
    /** @var integer The number of attempts that user has before being locked. */
    public $maxLoginAttempts = 5;

    public function events()
    {
        return [
            Model::EVENT_AFTER_VALIDATE => 'unsuccessfulAttemptsChecker',
        ];
    }

    /**
     * @param Event $event
     * @throws ForbiddenHttpException If the user is locked (for console applications)
     */
    public function unsuccessfulAttemptsChecker(Event $event)
    {
        $user = call_user_func([Yii::$app->user->identityClass, 'findByEmail'],
            ['email' => $event->sender->username]);
        if (!$user) {
            return;
        }
        
        if ($user->locked_until > time()) {
            Yii::info("The user [$user->email] is locked!", __CLASS__);
            throw new ForbiddenHttpException(Yii::t(Module::I18N_CATEGORY, 'Your account is locked!'));
        } else if($user->isLocked()) {
            Yii::info("Unlocking user [$user->email]!", __CLASS__);
            
            $user->unlock();
        }

        if ($event->sender->hasErrors('password')) {
            $user->login_attempts++;
            
            Yii::info("User [$user->email] has entered a wrong password. Login attempts count: $user->login_attempts", __CLASS__);
            if ($user->login_attempts == $this->maxLoginAttempts) {
                Yii::info("Locking user [$user->email]!", __CLASS__);

                $user->lock();
                throw new ForbiddenHttpException(Yii::t(Module::I18N_CATEGORY, 'Your account is locked!'));
            }

            $user->save(false);
        } else if ($user->login_attempts > 0) {
            Yii::info("Clear login attempts of user [$user->email]", __CLASS__);

            $user->login_attempts = 0;
            $user->save(false);
        }
    }
}
