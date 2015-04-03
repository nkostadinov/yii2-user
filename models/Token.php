<?php

namespace nkostadinov\user\models;

use Yii;

/**
 * This is the model class for table "token".
 *
 * @property integer $user_id
 * @property string $code
 * @property integer $created_at
 * @property integer $type
 * @property integer $expires_on
 *
 * @property User $user
 */
class Token extends \yii\db\ActiveRecord
{
    const TYPE_CONFIRMATION      = 0;
    const TYPE_RECOVERY          = 1;
    const TYPE_CONFIRM_NEW_EMAIL = 2;
    const TYPE_CONFIRM_OLD_EMAIL = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'code', 'created_at', 'type', 'expires_on'], 'required'],
            [['user_id', 'created_at', 'type', 'expires_on'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['user_id', 'code', 'type'], 'unique', 'targetAttribute' => ['user_id', 'code', 'type'], 'message' => 'The combination of User ID, Code and Type has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app.user', 'User ID'),
            'code' => Yii::t('app.user', 'Code'),
            'created_at' => Yii::t('app.user', 'Created At'),
            'type' => Yii::t('app.user', 'Type'),
            'expires_on' => Yii::t('app.user', 'Expires On'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('created_at', time());
            $this->setAttribute('code', \Yii::$app->security->generateRandomString());
        }
        return parent::beforeSave($insert);
    }

    public function getIsExpired()
    {
        return ($this->expires_on > 0) and ($this->expires_on < time());
    }
}
