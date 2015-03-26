<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 21.03.2015
 * Time: 09:10 ч.
 */

namespace nkostadinov\user\controllers;


use yii\web\Controller;

class BaseController extends Controller {

    public $viewPathOverride;

    public function getViewPath()
    {
        if(!isset($this->viewPathOverride))
            return parent::getViewPath();
        else
            return $this->viewPathOverride . DIRECTORY_SEPARATOR . $this->id;
    }

}