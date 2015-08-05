<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model nkostadinov\user\models\user */

$this->title = "{$model->DisplayName} #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => Yii::t('app.users', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="user-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a(Yii::t('app.users', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app.users', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app.users', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'username',
                'email:email',
                'StatusName',
                'created_at:datetime',
                'updated_at:datetime',
                'confirmed_on:datetime',
            ],
        ]) ?>


    </div>

<?php if ($model->tokens): ?>
    <h2>Active tokens</h2>
    <?= GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $model->tokens,
        ]),
        'columns' => [
            'created_at:datetime',
            'code',
            'expires_on:datetime',
            'name:text',
        ]
//        'columns' => $this->context->module->adminColumns,
    ]);
?>
<?php endif; ?>
<?php if ($model->userAccounts): ?>
    <h2>Linked accounts</h2>
    <?= GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $model->userAccounts,
        ]),
        'columns' => [
            'provider',
            'client_id',
            'token_create_time:datetime',
            'expires:datetime',
            [
                'value' => function($model) {
                    $data = json_decode($model->attributes);
                    $text = \yii\helpers\VarDumper::dumpAsString((array)$data, 10, true);
                    return $text;
                },
                'format' => 'raw',
            ],
//            'attributes:raw',
        ]
//        'columns' => $this->context->module->adminColumns,
    ]);
?>
<?php endif; ?>