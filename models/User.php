<?php
namespace nkostadinov\user\models;

use nkostadinov\user\Module;
use ReflectionClass;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $confirmed_on
 *
 * @property string $password write-only password
 *
 * @property Token[] $tokens
 * @property UserAccount[] $userAccounts
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['email', 'email'],
            [['email'], 'required', 'on' => 'register'],

            ['name', 'safe'],
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $token = Token::findOne([
            'code' => $token,
            'type' => Token::TYPE_API_AUTH
        ]);

        if (!isset($token) or $token->isExpired)
            throw new UnauthorizedHttpException(Yii::t(Module::I18N_CATEGORY, 'Auth code not found or expired!'));

        return static::findOne($token->user_id);
        //throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getUserAccounts()
    {
        return $this->hasMany(UserAccount::className(), ['user_id' => 'id']);
    }

    public function getPasswordHistories()
    {
        return $this->hasMany(PasswordHistory::className(), ['user_id' => 'id']);
    }

    public function confirm($code)
    {
        $token = Token::findOne([
            'code' => $code,
            'type' => Token::TYPE_CONFIRMATION,
            'user_id' => $this->id,
        ]);

        if (!isset($token) or $token->isExpired)
            throw new NotFoundHttpException(Yii::t(Module::I18N_CATEGORY, 'Confirmation code not found or expired!'));
        
        $token->delete();
        $this->confirmed_on = time();
        
        return $this->save(false);
    }

    public function getIsConfirmed()
    {
        return isset($this->confirmed_on);
    }

    public function getTokens()
    {
        return $this->hasMany(Token::className(), ['user_id' => 'id']);
    }

    public function getDisplayName()
    {
        return $this->name > '' ? $this->name : substr($this->email, 0, strpos($this->email, '@'));
    }

    public function getStatusName()
    {
        //reverse constant lookup :)
        foreach((new ReflectionClass(get_class()))->getConstants() as $name => $value) {
            if($value == $this->status && strpos($name, 'STATUS_') === 0)
                return substr($name, 7, 255);
        }
    }

    public function getLastLoginText()
    {
        return Yii::$app->formatter->asDatetime($this->last_login)."\n{$this->last_login_ip}";
    }

    public static function resetPassword($code)
    {
        $token = Token::findByCode($code);
        $user = $token->user;
        $user->setPassword(Yii::$app->security->generateRandomString());
        if ($user->save(false))
            $token->delete();

        return Yii::$app->user->login($user);
    }
}
