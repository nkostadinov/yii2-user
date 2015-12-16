<?php

namespace nkostadinov\user\models\forms;

use yii\base\Model;

/**
 * AcquireEmailForm is the model behind the acquire email form.
 */
class AcquireEmailForm extends Model
{
    public $email;
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
        ];
    }
}
