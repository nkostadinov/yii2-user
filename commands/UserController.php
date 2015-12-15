<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 26.03.2015
 * Time: 14:00 ч.
 */

namespace nkostadinov\user\commands;

use nkostadinov\user\models\forms\SignupForm;
use nkostadinov\user\models\Token;
use nkostadinov\user\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\Console;
use yii\web\NotFoundHttpException;

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
     * @throws InvalidConfigException
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
            $this->stdout(\Yii::t(Module::I18N_CATEGORY, 'Please fix following errors:') . "\n", Console::FG_RED);
            foreach ($model->errors as $errors) {
                foreach ($errors as $error) {
                    $this->stdout(" - ".$error."\n", Console::FG_RED);
                }
            }
        } else
            $this->stdout(\Yii::t(Module::I18N_CATEGORY, 'Successfully created user!') . "\n", Console::FG_RED);
    }

    /**
     * Confirms the account for the user.
     *
     * @param type $email The email of the user that is to be confirmed.
     */
    public function actionConfirm($email = null)
    {
        if (!$email) {
            $email = $this->prompt(Yii::t(Module::I18N_CATEGORY, 'Please enter the user\'s email:'), ['required' => true]);
        }
        
        try {
            $token = Token::findByUserEmail($email, Token::TYPE_CONFIRMATION);
            if ($token->user->confirm($token)) {
                $this->stdout(Yii::t(Module::I18N_CATEGORY, 'The user is successfuly confirmed!'), Console::FG_GREEN);
            } else {
                $this->stderr(Yii::t(Module::I18N_CATEGORY, 'Error while trying to confirm the user!'), Console::FG_RED);
            }
        } catch (NotFoundHttpException $ex) {
            $this->stderr($ex->getMessage(), Console::FG_RED);
        }
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
