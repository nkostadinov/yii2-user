<?php

return yii\helpers\ArrayHelper::merge(
    require('common.php'),
    [
        'components' => [
            'request' => [
                'enableCsrfValidation' => false,
                'enableCookieValidation' => false
            ],
        ],
    ]
);