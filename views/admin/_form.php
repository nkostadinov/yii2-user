<?php

use nkostadinov\user\models\User;
use nkostadinov\user\Module;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model nkostadinov\user\models\user */
/* @var $form ActiveForm */
?>

<div class="user-form row">

    <div class="col-lg-6 col-md-6">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->textInput() ?>
        <?= $form->field($model, 'email')->textInput() ?>
        <?= $form->field($model, 'status')->dropDownList([
            User::STATUS_ACTIVE => Yii::t(Module::I18N_CATEGORY, 'Active'),
            User::STATUS_DELETED => Yii::t(Module::I18N_CATEGORY, 'Deleted'),
        ]); ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t(Module::I18N_CATEGORY, 'Create') : Yii::t(Module::I18N_CATEGORY, 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
