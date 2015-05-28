<?php

namespace nkostadinov\user\models;

use nkostadinov\user\models\User;
use Yii;

/**
 * This is the model class for table "user_account".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $provider
 * @property string $attributes
 * @property string $access_token
 * @property integer $expires
 * @property integer $token_create_time
 * @property string $created_at
 * @property string $updated_at
 * @property bigint $client_id
 *
 * @property User $user
 */
class UserAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'expires', 'token_create_time'], 'integer'],
            [['attributes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['access_token'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'provider' => 'Provider',
            'attributes' => 'Attributes',
            'access_token' => 'Access Token',
            'expires' => 'Expires',
            'token_create_time' => 'Token Create Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'user_id']);
    }
}
