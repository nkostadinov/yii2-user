<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 26.03.2015
 * Time: 14:00 ч.
 */

namespace nkostadinov\user\commands;


use nkostadinov\user\models\forms\SignupForm;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Manages users via console interface.
 * @package nkostadinov\user\commands
 */
class UserController extends Controller {

    /**
     * Creates a new user using the RegisterForm instance. Every validation rule is also valid in console mode.
     *
     * @param null $email
     * @param null $username
     * @param null $password
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate($email = null, $username = null, $password = null)
    {
        /** @var SignupForm $model */
        $model = \Yii::createObject(Yii::$app->user->registerForm);

        foreach($model->attributes as $attribute=>$value) {
            $model->{$attribute} = $this->prompt($model->getAttributeLabel($attribute) . ':', [ 'required' => $model->isAttributeRequired($attribute) ]);
        }
        //try to create user or show error(s) if failed
        if(!$model->signup()) {
            $this->stdout(\Yii::t('app.user', 'Please fix following errors:') . "\n", Console::FG_RED);
            foreach ($model->errors as $errors) {
                foreach ($errors as $error) {
                    $this->stdout(" - ".$error."\n", Console::FG_RED);
                }
            }
        } else
            $this->stdout(\Yii::t('app.user', 'Successfully created user!') . "\n", Console::FG_RED);
    }

    public function actionConfirm($email)
    {
        //TODO:implement
    }

    public function actionReset($email)
    {
        //TODO:implement
    }

    public function actionDelete($email)
    {
        //TODO:implement
    }
}