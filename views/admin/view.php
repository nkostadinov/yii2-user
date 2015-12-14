<?php

use nkostadinov\user\Module;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model nkostadinov\user\models\user */

$this->title = "{$model->DisplayName} #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::I18N_CATEGORY, 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="user-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a(Yii::t(Module::I18N_CATEGORY, 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t(Module::I18N_CATEGORY, 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t(Module::I18N_CATEGORY, 'Are you sure you want to delete this item?'),
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
        'dataProvider' => new ArrayDataProvider([
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
        'dataProvider' => new ArrayDataProvider([
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
                    $text = VarDumper::dumpAsString((array)$data, 10, true);
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