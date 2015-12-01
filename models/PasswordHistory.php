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

    /**
     * @param integer $userId The user's id
     * @param string $passwordHash The passsword's hash
     */
    public static function createAndSave($userId, $passwordHash)
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->user_id = $userId;
        $passwordHistory->password_hash = $passwordHash;
        $passwordHistory->save();
    }

    /**
     * @param integer $userId
     * @param integer $limit
     * @return array An array of PasswordHistory objects
     */
    public static function findAllByUserId($userId, $limit = 5)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->limit($limit)
            ->all();
    }
}
