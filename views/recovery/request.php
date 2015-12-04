<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 29.03.2015
 * Time: 19:21 ч.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var nkostadinov\user\models\RecoveryForm $model
 */
$this->title = Yii::t('app.user', 'Recover your password');
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
                    'id' => 'password-recovery-form',
                    //'enableAjaxValidation'   => true,
                    //'enableClientValidation' => false
                ]); ?>

                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

                <?= Html::submitButton(Yii::t('app.user', 'Continue'), ['class' => 'btn btn-primary btn-block', 'name' => 'password-recovery-button']) ?><br>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>