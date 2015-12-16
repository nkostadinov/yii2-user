# Password History Policy

When a user wants to change his password, he must add a password that hasn't been used by him in the past.
This functionality checks the last 5 password changes. The value is configurable.

# Installation

In order for this extension to work, the `password_history` table must be created. 
Because of that, if you haven't run the advanced migrations so far, please do it now:

```
$ ./yii migrate --migrationPath=@vendor/nkostadinov/yii2-user/migrations/advanced
```

Then you must add the PasswordHistoryPolicyBehavior to your ChangePasswordForm model in your config (config.php/web.php).

This is the most simple configuration needed:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'changePasswordForm' => [
        'class' => 'nkostadinov\user\models\forms\ChangePasswordForm',
        'as passwordHistoryPolicy' => [ // Or whatever name you choose
            'class' => 'nkostadinov\user\behaviors\PasswordHistoryPolicyBehavior',
        ],
    ],
],
```

If you want to change the number of passwords checked, add one more line:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'changePasswordForm' => [
        'class' => 'nkostadinov\user\models\forms\ChangePasswordForm',
        'as passwordHistoryPolicy' => [ // Or whatever name you choose
            'class' => 'nkostadinov\user\behaviors\PasswordHistoryPolicyBehavior',
            'lastPasswordChangesCount' => 10, // Check the last 10 passwords
        ],
    ],
],
```
