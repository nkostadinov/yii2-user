# Password Aging

The password aging functionality is designed to be used in enterprise environments, by adding more security to the application.
The functionality requires a user to change his password after a certain period of time.
By default this period is set to two months (defined in seconds), but the value is configurable.

Here is the life cycle of the functionality:
 - The user logs in to the application as usual;
 - If the user hasn't changed his password for a long time, the user is logged out and redirected to the password change form (In console environment a ForbiddenHttpException is thrown); 
 - On the password change form the user is required to add his email, old password and the new password;
 - On success the user's password is changed and the user is logged in to the application.

# Installation

In order for this extension to work, the `password_changed_at` field must be present in the user's table. 
Because of that, if you haven't run the advanced migrations so far, please do it now:

```
$ ./yii migrate --migrationPath=@vendor/nkostadinov/yii2-user/migrations/advanced
```

In order to install the password aging functionality, you must add the PasswordAgingBehavior to your config (config.php/web.php).

This is the most simple configuration needed:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'as passwordAging' => [ // or whatever name you choose
        'class' => 'nkostadinov\user\behaviors\PasswordAgingBehavior',
    ],
],
```

If you want to change the time interval, after which the user must change his password, just add the following line:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'as passwordAging' => [
        'class' => 'nkostadinov\user\behaviors\PasswordAgingBehavior',
        'passwordChangeInterval' => 60 * 60 * 24 * 3, // Three months (in seconds)
    ],
],
```

If you want to customize the view, do it from the module:

```
'modules' => [
    ...
    'user' => [
        'class' => 'nkostadinov\user\Module',
        'changePasswordView' => '@path/to/your/view'
    ],
    ...
],
```