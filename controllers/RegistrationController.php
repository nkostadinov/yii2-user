<?php

namespace nkostadinov\user\controllers;

use nkostadinov\user\models\forms\SignupForm;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class RegistrationController extends \yii\web\Controller
{
    public function actionConfirm()
    {
        return $this->render('confirm');
    }

    public function actionSignup()
    {
        if (!$this->module->allowRegistration) {
            throw new NotFoundHttpException("Registration disabled!");
        }

        $model = new SignupForm();

        $data = [];
        $account = null;
        if(Yii::$app->session->has('registration'))
        {
            $session = Yii::$app->getSession()->get('registration');
            $account = UserAccount::findOne($session['auth_account_id']);
            $attributes = json_decode($account->attributes);

            $data['SignupForm'] = [
                'username' => $attributes->name,
                'email' => $attributes->email,
            ];
            Yii::$app->getSession()->remove('registration');
        } elseif(isset($_POST['UserAccount']))
            $account = UserAccount::findOne($_POST['UserAccount']['id']);

        if ($model->load(ArrayHelper::merge(Yii::$app->request->post(), $data))) {
            if ($user = $model->signup($account)) {
                if (Yii::$app->getUser()->login($user)) {
                    Yii::$app->getSession()->remove('registration');
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
            'account' => $account,
        ]);
    }

}
