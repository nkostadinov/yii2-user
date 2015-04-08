<?php

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME']     = YII_TEST_ENTRY_URL;

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