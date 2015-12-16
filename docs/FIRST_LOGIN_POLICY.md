# First Login Policy

Requires the user to change password before the first login or when required.

# Installation

In order for this extension to work, the `require_password_change` field must be present in the user's table. 
Because of that, if you haven't run the advanced migrations so far, please do it now:

```
$ ./yii migrate --migrationPath=@vendor/nkostadinov/yii2-user/migrations/advanced 
```

Add the FirstLoginPolicyBehavior to the user component in your config (config.php/web.php).

Sample configuration:

```
'user' => [
    'class' => 'nkostadinov\user\components\User',
    'as firstLoginPolicy' => [ // Or whatever name you choose
        'class' => 'nkostadinov\user\behaviors\FirstLoginPolicyBehavior'
    ],    
],
```
