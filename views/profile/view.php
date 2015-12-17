<?php

use nkostadinov\user\Module;
use yii\authclient\widgets\AuthChoice;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\VarDumper;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
$model = Yii::$app->user->identity;
$this->title = "{$model->displayName} #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => Yii::t(Module::I18N_CATEGORY, 'Profile'), 'url' => ['view']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= DetailView::widget([
    'model' => $model,
    'attributes' => [
        'username',
        'email:email',
        'statusName',
        'created_at:datetime',
        'updated_at:datetime',
        'confirmed_on:datetime',
    ],
])?>

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
        ]
    ]);
?>
<?php endif; ?>
    
<?php if (Yii::$app->get('authClientCollection', false)): ?>
<h2>Account linking</h2>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'Link accounts',
                'format' => 'raw',
                'value' => AuthChoice::widget([
                    'baseAuthUrl' => [ '/'.$this->context->module->id . '/security/auth'],
                    'popupMode' => false,
                ]),
            ]
        ],
    ])?>
<?php endif; ?>