<?php

namespace nkostadinov\user\models;

use nkostadinov\user\interfaces\IUserAccount;
use nkostadinov\user\models\User;
use nkostadinov\user\Module;
use Yii;
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
            'id' => Yii::t(Module::I18N_CATEGORY, 'ID'),
            'user_id' => Yii::t(Module::I18N_CATEGORY, 'User ID'),
            'provider' => Yii::t(Module::I18N_CATEGORY, 'Provider'),
            'attributes' => Yii::t(Module::I18N_CATEGORY, 'Attributes'),
            'access_token' => Yii::t(Module::I18N_CATEGORY, 'Access Token'),
            'expires' => Yii::t(Module::I18N_CATEGORY, 'Expires'),
            'token_create_time' => Yii::t(Module::I18N_CATEGORY, 'Token Create Time'),
            'created_at' => Yii::t(Module::I18N_CATEGORY, 'Created At'),
            'updated_at' => Yii::t(Module::I18N_CATEGORY, 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }

    public static function createAndSave(IUserAccount $client)
    {
        $token = $client->getAccessToken();

        $account = new UserAccount();
        $account->provider = $client->getId();
        $account->attributes = json_encode($client->getUserAttributes());
        $account->access_token = $token->token;
        $account->expires = $token->createTimestamp + $token->expireDuration;
        $account->token_create_time = $token->createTimestamp;
        $account->client_id = $client->getUserId();
        $account->save(false);

        return $account;
    }

    public static function findByClient(IUserAccount $client)
    {
        return UserAccount::find()
            ->with('user')
            ->where([
                'provider' => $client->id,
                'client_id' => $client->getUserId(),
            ])->one();
    }
}
