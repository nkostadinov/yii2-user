<?php

namespace nkostadinov\user\models;

use nkostadinov\user\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
class UserAccount extends ActiveRecord
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
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }

    public static function createAndSave(ClientInterface $client)
    {
        $token = $client->getAccessToken();

        $account = new UserAccount();
        $account->provider = $client->getName();
        $account->attributes = json_encode($client->getUserAttributes());
        $account->access_token = $token->token;
        $account->expires = $token->createTimestamp + $token->expireDuration;
        $account->token_create_time = $token->createTimestamp;
        $account->client_id = $client->getUserAttributes()['id'];
        $account->save(false);

        return $account;
    }

    public static function findByClient(ClientInterface $client)
    {
        return UserAccount::find()
            ->with('user')
            ->where([
                'provider' => $client->name,
                'client_id' => $client->getUserAttributes()['id']
            ])->one();
    }
}
