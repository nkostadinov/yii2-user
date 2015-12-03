<?php

namespace nkostadinov\user\behaviors;

use nkostadinov\user\models\User;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Model;
use yii\web\ForbiddenHttpException;

/**
 * A behavior that locks the user if a wrong password is added in 5 consequent times.
 * The account is locked for an hour.
 */
class UnsuccessfulLoginAttemptsBehavior extends Behavior
{
    /** @var integer The number of attempts that user has before being locked. */
    public $maxLoginAttempts = 5;

    /** @var integer The time for which the use is locked. Defaults to 1 hour (in seconds). */
    public $lockExpiration = 3600;

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
        $user = User::findByEmail($event->sender->username);
        $currentTime = time();
        
        if ($user->locked_until > $currentTime) {
            throw new ForbiddenHttpException('Your account is locked!');
        } else if(isset($user->locked_until) && $user->locked_until < $currentTime) {
            // Unlock the account
            $user->login_attempts = 0;
            $user->locked_until = null;
        }

        if ($event->sender->hasErrors('password')) {
            $user->login_attempts++;
            if ($user->login_attempts == $this->maxLoginAttempts) {
                // Lock the account
                $user->locked_until = $currentTime + $this->lockExpiration;
                $user->save(false);

                throw new ForbiddenHttpException('Your account is locked!');
            }
        } else if ($user->login_attempts > 0) {
            // Clear the attempts
            $user->login_attempts = 0;
        }

        $user->save(false);
    }
}
