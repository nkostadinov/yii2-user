<?php

namespace nkostadinov\user;

use nkostadinov\user\interfaces\ISecurityPolicy;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'nkostadinov\user\controllers';

    /**
     * @var bool Whether to allow new user to register.
     */
    public $allowRegistration = true;
    /**
     * @var bool Whether to allow new user to register.
     */
    public $allowPasswordRecovery = true;

    public $views = [
        'register' => '@nkostadinov/user/views/registration/signup',
        'login' => '@nkostadinov/user/views/security/login',
        'confirm' => '@nkostadinov/user/views/registration/confirm',
        'request' => '@nkostadinov/user/views/recovery/request',
    ];

    public $adminColumns = [
        //['class' => 'yii\grid\SerialColumn'],
        'DisplayName',
        'email:email',
//            'status',
        'created_at:datetime',
        'confirmed_on:datetime',
        'last_login:datetime',
        'last_login_ip:text',
        'register_ip:text',
        ['class' => 'yii\grid\ActionColumn'],
    ];

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

}
