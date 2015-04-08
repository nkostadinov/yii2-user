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
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@vendor' => VENDOR_DIR,
        '@bower' => VENDOR_DIR . '/bower',
        '@nkostadinov/user' => realpath(__DIR__ . '/../../../' ),
    ],
    'modules' => [
        'user' => 'nkostadinov\user\Module',
    ],
    'components' => [
        'db' => require(__DIR__ . '/db.php'),
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'assetManager' => [
            'basePath' => dirname(__DIR__) . '/assets',
        ],
        'user' => [
            'class' => 'nkostadinov\user\components\user'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,

        ],
    ]
];

return $config;