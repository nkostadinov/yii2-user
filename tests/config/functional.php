<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 02.04.2015
 * Time: 14:11 ч.
 */

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME']     = YII_TEST_ENTRY_URL;

$config = [
    'id' => 'tester',
    'name' => 'Yii2 user test app',
    'basePath' => dirname(__DIR__) . '/_app/',
    'aliases' => [
        '@vendor' => VENDOR_DIR,
    ],
    'modules' => [
        'user' => 'nkostadinov\user\Module',
    ],
    'components' => [
        'assetManager' => [
            'basePath' => dirname(__DIR__) . '/_app/assets',
        ],
        'user' => [
            'class' => 'nkostadinov\user\components\user'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,

        ],
        'request' => [
            'enableCsrfValidation'   => false,
            'enableCookieValidation' => false
        ],
    ]
];

return $config;