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
        'register' => '@vendor/nkostadinov/yii2-user/views/registration/signup',
        'login' => '@vendor/nkostadinov/yii2-user/views/security/login',
        //'confirm' => '@vendor/nkostadinov/yii2-user/views/registration/confirm',
        'request' => '@vendor/nkostadinov/yii2-user/views/recovery/request',
    ];

    /**
     * @var ISecurityPolicy the security policy implementation
     */
    public $security = 'nkostadinov\user\components\Security';

    private $_components;

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    /**
     * @return ISecurityPolicy The security policy implementation
     * @throws InvalidConfigException
     */
    public function getSecurity()
    {
        if(!isset($this->_components['security'])) {
            $this->_components['security'] = \Yii::createObject($this->security);
            if(!$this->_components['security'] instanceof ISecurityPolicy)
                throw new InvalidConfigException();
        }
        return $this->_components['security'];
    }
}
