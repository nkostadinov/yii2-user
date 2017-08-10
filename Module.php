<?php

namespace nkostadinov\user;

use nkostadinov\user\commands\UserController;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

class Module extends \yii\base\Module implements BootstrapInterface
{
    const I18N_CATEGORY = 'nkostadinov.user';

    public $controllerNamespace = 'nkostadinov\user\controllers';

    /** @var array The rules to be used in URL management. */
    public static $urlRules = [
        '<action:(login|logout)>'     => 'user/security/<action>',
        '<action:(signup|resend)>'    => 'user/registration/<action>',
        'confirm/<code:[\w-]+>'       => 'user/registration/confirm',
        'forgotten-password'          => 'user/recovery/request',
        'reset/<code:[\w-]+>'         => 'user/recovery/reset',
        'change-password'             => 'user/security/change-password',
        'acquire-email'               => 'user/security/acquire-email',
        'acquire-password'            => 'user/security/acquire-password',
        'profile'                     => 'user/profile/view',
    ];

    /**
     * @var bool Whether to register URL rules for this module (defined in static var $urlRules)
     */
    public $registerUrls = true;

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
    public $acquireEmailView = '@nkostadinov/user/views/security/acquire_email';
    public $acquirePasswordView = '@nkostadinov/user/views/security/acquire_password';
    public $resetPasswordView = '@nkostadinov/user/views/recovery/reset';

    public $adminColumns = [
        //['class' => 'yii\grid\SerialColumn'],
        'displayName',
        'email:email',
        'statusName',
        'created_at:datetime',
        'confirmed_on:datetime',
        'lastLoginText:html:Last login',
//        'last_login:datetime',
//        'last_login_ip:text',
        'register_ip:text',
        [
            'class' => 'yii\grid\ActionColumn',
            'header' => 'Actions',
        ],
    ];

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            if(!isset($app->controllerMap[$this->id]))
                $app->controllerMap[$this->id] = [
                    'class' => UserController::className(),
                ];
        } else if ($app instanceof \yii\web\Application) {
            $module = $app->getModule('user');
            if(($module instanceof self) && $module->registerUrls)
                $app->urlManager->addRules(self::$urlRules);
        }

        if (!isset($app->get('i18n')->translations[self::I18N_CATEGORY])) {
            $app->get('i18n')->translations[self::I18N_CATEGORY] = [
                'class'    => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
}
