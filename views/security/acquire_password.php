<?php

use nkostadinov\user\models\forms\LoginForm;
use nkostadinov\user\Module;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model LoginForm */
/* @var $module Module */

$this->title = Yii::t(Module::I18N_CATEGORY, 'Link accounts');
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
                    'id' => 'acquire-password-form',
                    'options' => ['class' => 'form-vertical'],
                ]); ?>

                <?= $form->field($model, 'username')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'password', ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control']])
                    ->passwordInput()
                    ->label(Yii::t(Module::I18N_CATEGORY, 'Password')) ?>
                
                <div class="form-group">
                    <?= Html::submitButton(Yii::t(Module::I18N_CATEGORY, 'Submit'), ['class' => 'btn btn-primary btn-block', 'name' => 'acquire-email-button']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <p class="text-center">
            <?= 'Don\'t want to link your account? ' . Html::a(Yii::t(Module::I18N_CATEGORY, 'Create a new one!'), ['acquire-email']) ?>
        </p>
    </div>
</div>