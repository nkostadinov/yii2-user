<?php
namespace nkostadinov\user\assets;

use yii\web\AssetBundle;

class NkostadinovUserAsset extends AssetBundle
{
    public $sourcePath = '@nkostadinov/user/web';

    public $js = [
        'js/nkostadinov-user.js',
    ];
}
