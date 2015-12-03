# First Login Policy [![Build Status](https://travis-ci.org/nkostadinov/yii2-user.svg?branch=master)](https://travis-ci.org/nkostadinov/yii2-user) 

Requires the user to change password before the first login or when required.

# Installation

First of all, run the migrations:

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
