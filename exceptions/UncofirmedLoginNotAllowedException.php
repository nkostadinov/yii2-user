<?php
/**
 * Created by PhpStorm.
 * User: Nikola Kostadinov
 * Date: 19.03.2019
 * Time: 21:52
 */

namespace nkostadinov\user\exceptions;

use yii\web\ForbiddenHttpException;

class UncofirmedLoginNotAllowedException extends ForbiddenHttpException
{

}