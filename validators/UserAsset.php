<?php
namespace nkostadinov\user\validators;

use yii\web\AssetBundle;

class UserAsset extends AssetBundle
{
    public $sourcePath = '@nkostadinov/user/assets';

    public $js = [
        'js/user.js',
    ];
}
