<?php

return \yii\helpers\ArrayHelper::merge(
    require('common.php'),
    [
        'controllerMap' => [
            'migrate' => [
                'class' => 'yii\console\controllers\MigrateController',
                'migrationPath' => __DIR__ . '/../../../migrations'
            ]
        ],
    ]
);