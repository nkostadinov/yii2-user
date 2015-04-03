<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 02.04.2015
 * Time: 14:11 ч.
 */

$config = [
    'id' => 'tester',
    'name' => 'Yii2 user test app',
    'basePath' => __DIR__,
    'modules' => [
        'user' => 'nkostadinov\user\Module',
    ],
    'components' => [
        'user' => [
            'class' => 'nkostadinov\user\components\user'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,

        ]
    ]
];

return $config;