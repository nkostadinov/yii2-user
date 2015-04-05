# Yii2 User module 

Just another user module management functionalities.

* Optional self registration via front end
* Lost password retrieval(optional)
* User administration interface
* Flexible access control
* Console commnads(TODO)

Installation
------------

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
        'class' => 'nkostadinov\user\models\User',
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

Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \nkostadinov\user\AutoloadExample::widget(); ?>```