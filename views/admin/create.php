<?php

use nkostadinov\user\Module;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/* @var $model nkostadinov\user\models\user */

$this->title = Yii::t(Module::I18N_CATEGORY, 'Create {modelClass}', [
    'modelClass' => 'User',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::I18N_CATEGORY, 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
