# Password aging [![Build Status](https://travis-ci.org/nkostadinov/yii2-user.svg?branch=master)](https://travis-ci.org/nkostadinov/yii2-user) 

The password aging functionality is designed to be used in enterprise environments, by adding more security to the application. 
The password aging behavior requires a user to change his password after a certain time interval. 
By default this interval is set to two months (defined in seconds), but the value is configurable.

Here is the life cycle of the functionality:
 - The user logs in to the application as usual;
 - If the user hasn't changed his password for a long time the user is logged out and redirected to the password change form;
 - On the password change form the user is required to add his email, old password and the new password;
 - On success the user's password is changed and the user is logged in to the application.

# Installation

In order to install the password aging functionality, you must add the password 
aging behavior to your config (config.php/web.php).

This is the most simple configuration needed:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'as passwordAging' => [
        'class' => 'nkostadinov\user\behaviors\PasswordAgingBehavior',
    ],
],
```

**Just remember that the name of the behavior must be 'passwordAging'.**

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

Here are the rest of the configurations:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'as passwordAging' => [
        'class' => 'nkostadinov\user\behaviors\PasswordAgingBehavior',
        'passwordChangeInterval' => 60 * 60 * 24 * 3, // Three months (in seconds)
        'changePasswordUrl' => [...], // The route to the change password form as an array
        'changePasswordForm' => ..., // The change password form model
        'changePasswordView' => ..., // The change password view file
    ],
],
```