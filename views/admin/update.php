<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model nkostadinov\user\models\user */

$this->title = Yii::t('app.users', 'Update {modelClass}: ', [
    'modelClass' => 'User',
]) . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app.users', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app.users', 'Update');
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
