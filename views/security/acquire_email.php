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

$this->title = Yii::t(Module::I18N_CATEGORY, 'One more step');
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
                    'options' => ['class' => 'form-vertical'],
                ]); ?>

                <?= $form->field($model, 'email'); ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t(Module::I18N_CATEGORY, 'Submit'), ['class' => 'btn btn-primary btn-block']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
