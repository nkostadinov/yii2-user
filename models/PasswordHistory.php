<?php
namespace nkostadinov\user\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $password_hash
 * @property timestamp $created_at
 *
 * @property User $user
 */
class PasswordHistory extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%password_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password_hash', 'required'],
            ['password_hash', 'string', 'max' => 255],
        ];
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
