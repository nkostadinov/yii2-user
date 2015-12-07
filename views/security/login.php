<?php

use nkostadinov\user\models\forms\LoginForm;
use nkostadinov\user\Module;
use yii\authclient\widgets\AuthChoice;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model LoginForm */
/* @var $module Module */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['class' => 'form-vertical'],
                ]); ?>

                <?= $form->field($model, 'username', ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control',]]) ?>
                <?= $form->field($model, 'password')->passwordInput()->label(Yii::t('user', 'Password') . ($module->allowPasswordRecovery ? ' (' . Html::a(Yii::t('user', 'Forgot password?'), ['/user/recovery/request'], ['tabindex' => '5']) . ')' : '')) ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="form-group">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <?php if ($module->allowRegistration): ?>
            <p class="text-center">
                <?= Html::a(Yii::t('user', 'Don\'t have an account yet? Sign up!'), ['/user/registration/signup']) ?>
            </p>
        <?php endif ?>

        <?php if (Yii::$app->get("authClientCollection", false)): ?>
            <div>
                <?= AuthChoice::widget([
                    'baseAuthUrl' => [ '/'.$this->context->module->id . '/security/auth']
                ]) ?>
            </div>
        <?php endif; ?>

    </div>
</div>