# Yii2 User module [![Build Status](https://travis-ci.org/nkostadinov/yii2-user.svg?branch=master)](https://travis-ci.org/nkostadinov/yii2-user) 

Just another user module management functionalities.

* Optional self registration via front end
* Lost password retrieval(optional)
* User administration interface
* Flexible access control
* Console commnads(TODO)
* Advanced user (optional)

# Installation

1. Download Yii2-user using composer
--------------------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist nkostadinov/yii2-user "*"
```

or add

```
"nkostadinov/yii2-user": "*"
```

to the require section of your `composer.json` file.

2. Configure your application
-------------------------

Add following lines to your main configuration file:

```php
'components' => [
    'user' => [
        'class' => 'nkostadinov\user\components\User',
    ],
],
```

```php
'modules' => [
    'user' => [
        'class' => 'nkostadinov\user\Module',
    ],
],
```

Step 3: Update database schema
------------------------------

> **NOTE:** Make sure that you have properly configured **db** application component.

After you downloaded and configured Yii2-user, the last thing you need to do is updating your database schema by
applying the migrations:

```bash
$ php yii migrate/up --migrationPath=@vendor/nkostadinov/yii2-user/migrations
```

# How to run tests

Install codeception globally:

```bash
$ composer global require "codeception/codeception:*"
```

Install globally the composer's asset plugin:

```bash
$ composer global require "fxp/composer-asset-plugin:~1.1.0"
```

Go to nkostadinov/yii2-user directory and run:

```bash
$ composer update
```

Build the codeception actors:

```bash
$ codecept build
```

Create a new database called 'user_test':

```bash
$ mysql -e 'create database user_test;'
```

Run the migrations:

```bash
$ php tests/_app/yii migrate --interactive=0
```

# Advanced user

The yii2-user extension has the following additional functionalities that can be added on demand:

 - [Password aging] (PASSWORD_AGING.md)
