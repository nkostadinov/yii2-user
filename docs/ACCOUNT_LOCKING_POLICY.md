# Account Locking Policy

A functionality that locks the user after 5 consequent unsuccessful attempts to login. The user is locked for 1 hour. 
Those values are configurable.

# Installation

In order for this extension to work, the `login_attempts` and `locked_until` fields must be present in the user's table. 
Because of that, if you haven't run the advanced migrations so far, please do it now:

```
$ ./yii migrate --migrationPath=@vendor/nkostadinov/yii2-user/migrations/advanced 
```

Just add the UnsuccessfulLoginAttemptsBehavior to your LoginForm model in your config (config.php/web.php).

Sample configuration:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'loginForm' => [
        'class' => 'nkostadinov\user\models\forms\LoginForm',
        'as unsuccessfulLoginAttempts' => [ // Or whatever name you choose
            'class' => 'nkostadinov\user\behaviors\UnsuccessfulLoginAttemptsBehavior',
        ],
    ],
],
```

Configurations:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'lockExpiration' => 7200, // Locked for 2 hours (in seconds)
    'loginForm' => [
        'class' => 'nkostadinov\user\models\forms\LoginForm',
        'as unsuccessfulLoginAttempts' => [ // Or whatever name you choose
            'class' => 'nkostadinov\user\behaviors\UnsuccessfulLoginAttemptsBehavior',
            'maxLoginAttempts' => 10, // Locked after 10 unsuccessful attempts
        ],
    ],
],
```
