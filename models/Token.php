<?php

namespace nkostadinov\user\models;

use nkostadinov\user\Module;
use ReflectionClass;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

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
class Token extends ActiveRecord
{
    const TYPE_CONFIRMATION      = 0;
    const TYPE_RECOVERY          = 1;
    const TYPE_CONFIRM_NEW_EMAIL = 2;
    const TYPE_CONFIRM_OLD_EMAIL = 3;
    const TYPE_API_AUTH          = 4;


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
            [['user_id', 'code', 'type'], 'unique', 'targetAttribute' => ['user_id', 'code', 'type'], 'message' => Yii::t(Module::I18N_CATEGORY, 'The combination of User ID, Code and Type has already been taken.')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t(Module::I18N_CATEGORY, 'User ID'),
            'code' => Yii::t(Module::I18N_CATEGORY, 'Code'),
            'created_at' => Yii::t(Module::I18N_CATEGORY, 'Created At'),
            'type' => Yii::t(Module::I18N_CATEGORY, 'Type'),
            'expires_on' => Yii::t(Module::I18N_CATEGORY, 'Expires On'),
        ];
    }

    /**
     * @return ActiveQuery
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

    public function getName()
    {
        //reverse constant lookup :)
        foreach((new ReflectionClass(get_class()))->getConstants() as $name => $value) {
            if($value == $this->type)
                return $name;
        }
    }

    /**
     * Finds a token with user by the token's code.
     *
     * @param string $code
     * @param integer $type The type of the token
     * @return Token
     * @throws NotFoundHttpException
     */
    public static function findByCode($code, $type = self::TYPE_RECOVERY)
    {
        $token = Token::find()->with('user')
            ->where(['code' => $code, 'type' => $type])
            ->one();
        
        if (empty($token) || empty($token->user)) {
            throw new NotFoundHttpException(Yii::t(Module::I18N_CATEGORY, 'Token not found!'));
        }

        return $token;
    }

    /**
     * Finds a token with user by the user's email.
     *
     * @param string $email The user's email
     * @param integer $type The token's type. By default Token::TYPE_CONFIRMATION
     * @return Token The token if found
     * @throws NotFoundHttpException If the token is not found
     */
    public static function findByUserEmail($email, $type = self::TYPE_CONFIRMATION)
    {
        $token = Token::find()
            ->select('*')
            ->leftJoin(User::tableName(), 'user.id = token.user_id')
            ->where(['user.email' => $email, 'user.status' => User::STATUS_ACTIVE, 'type' => $type])
            ->one();
        
        if (empty($token)) {
            throw new NotFoundHttpException(Yii::t(Module::I18N_CATEGORY, 'Token not found!'));
        }

        return $token;
    }

    /**
     * Defining composite primary key
     * @return array
     */
    public function getPrimaryKey($asArray = false)
    {
        return ['user_id', 'code', 'type'];
    }

    public static function createRecoveryToken($userId)
    {
        return static::createToken($userId, self::TYPE_RECOVERY);
    }

    private static function createToken($userId, $type)
    {
        $token = Yii::createObject([
            'class' => static::className(),
            'user_id' => $userId,
            'type' => $type,
        ]);
        $token->save(false);
        return $token;
    }
}
