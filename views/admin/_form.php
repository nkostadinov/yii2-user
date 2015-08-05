<?php

use nkostadinov\user\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model nkostadinov\user\models\user */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form row">

    <div class="col-lg-6 col-md-6">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->textInput() ?>
        <?= $form->field($model, 'email')->textInput() ?>
        <?= $form->field($model, 'status')->dropDownList([
            User::STATUS_ACTIVE => Yii::t('app.users', 'Active'),
            User::STATUS_DELETED => Yii::t('app.users', 'Deleted'),
        ]); ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app.users', 'Create') : Yii::t('app.users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
