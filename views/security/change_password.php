<?php

use nkostadinov\user\models\forms\ChangePasswordForm;
use nkostadinov\user\Module;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model ChangePasswordForm */
/* @var $module Module */

$this->title = Yii::t(Module::I18N_CATEGORY, 'Change password');
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
                    'id' => 'change-password-form',
                    'options' => ['class' => 'form-vertical'],
                ]); ?>

                <?php if ($model->scenario == ChangePasswordForm::SCENARIO_REQUIRE_PASSWORD_CHANGE): ?>
                <?= $form->field($model, 'email', ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control']]) ?>
                <?= $form->field($model, 'oldPassword')->passwordInput() ?>
                <?php endif; ?>
                <?= $form->field($model, 'newPassword')->passwordInput() ?>
                <?= $form->field($model, 'newPasswordRepeat')->passwordInput() ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t(Module::I18N_CATEGORY, 'Submit'), ['class' => 'btn btn-primary btn-block', 'name' => 'change-password-button']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>