<?php

use nkostadinov\user\Module;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var ActiveForm $form
 * @var nkostadinov\user\models\RecoveryForm $model
 */
$this->title = Yii::t(Module::I18N_CATEGORY, 'Reset your password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin(['id' => 'reset-form']); ?>

                <?= $form->field($model, 'newPassword')->passwordInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'newPasswordRepeat')->passwordInput() ?>

                <?= Html::submitButton(Yii::t(Module::I18N_CATEGORY, 'Continue'), ['class' => 'btn btn-primary btn-block']) ?><br>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>