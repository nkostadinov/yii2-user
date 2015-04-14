<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 25.03.2015
 * Time: 13:56 ч.
 */

namespace nkostadinov\user\components;


use nkostadinov\user\interfaces\ISecurityPolicy;
use nkostadinov\user\interfaces\IUserNotificator;
use nkostadinov\user\models\User as UserModel;
use nkostadinov\user\models\UserSearch;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\User as BaseUser;

class User extends BaseUser
{
    public $identityClass = 'nkostadinov\user\models\User';

    public $loginForm = 'nkostadinov\user\models\forms\LoginForm';
    public $registerForm = 'nkostadinov\user\models\forms\SignupForm';
    public $recoveryForm = 'nkostadinov\user\models\forms\RecoveryForm';

    public $enableConfirmation = true;
    public $allowUncofirmedLogin = false;
    public $requireUsername = false;

    public $loginUrl = ['user/security/login'];

    public $components = [
        'notificator' => 'nkostadinov\user\components\MailNotificator',
        'security' => 'nkostadinov\user\components\Security',
    ];

    private $_notificator;
    private $_security;

    public function listUsers($params)
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($params);
        return $dataProvider;
    }

    public function findUserByEmail($email)
    {
        return UserModel::findOne([
            'email' => $email,
        ]);
    }

    /**
     * @return IUserNotificator
     */
    public function getNotificator()
    {
        if(!isset($this->_notificator)) {
            $this->_notificator = \Yii::createObject($this->components['notificator']);
            //Instance::ensure($this->_security, 'nkostadinov\user\interfaces\IUserNotificator');
        }
        return $this->_notificator;
    }

    public function can($permissionName, $params = [], $allowCaching = true)
    {
        //TODO: add cache
        return $this->getSecurity()->hasAccess($permissionName, $params);
    }


    /**
     * @return ISecurityPolicy The security policy implementation
     */
    public function getSecurity()
    {
        if(!isset($this->_security)) {
            $this->_security = \Yii::createObject($this->components['security']);
            //Instance::ensure($this->_security, 'nkostadinov\user\interfaces\ISecurityPolicy');
        }
        return $this->_security;
    }
}