<?php

namespace nkostadinov\user;

use nkostadinov\user\interfaces\ISecurityPolicy;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'nkostadinov\user\controllers';

    /** @var array The rules to be used in URL management. */
    public static $urlRules = [
        'profile'                     => 'user/profile/view',
        '<action:(login|logout)>'     => 'user/security/<action>',
        '<action:(register|resend)>'  => 'user/registration/<action>',
        'confirm/<id:\d+>/<code:\w+>' => 'user/registration/confirm',
        'forgot'                      => 'user/recovery/request',
        'recover/<code:\w+>'          => 'user/recovery/reset',
        'changePassword'              => 'user/security/change-password',
    ];

    /**
     * @var bool Whether to allow new user to register.
     */
    public $allowRegistration = true;
    /**
     * @var bool Whether to allow new user to register.
     */
    public $allowPasswordRecovery = true;

    public $registerView = '@nkostadinov/user/views/registration/signup';
    public $loginView = '@nkostadinov/user/views/security/login';
    public $confirmView = '@nkostadinov/user/views/registration/confirm';
    public $requestView = '@nkostadinov/user/views/recovery/request';
    public $profileView = '@nkostadinov/user/views/profile/view';
    public $changePasswordView = '@nkostadinov/user/views/security/change_password';

    public $adminColumns = [
        //['class' => 'yii\grid\SerialColumn'],
        'DisplayName',
        'email:email',
        'statusName',
        'created_at:datetime',
        'confirmed_on:datetime',
        'lastLoginText:html:Last login',
//        'last_login:datetime',
//        'last_login_ip:text',
        'register_ip:text',
        ['class' => 'yii\grid\ActionColumn'],
    ];

    public function init()
    {
        parent::init();

        // custom initialization code goes here!!!
    }

}
