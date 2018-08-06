<?php
namespace nkostadinov\user\models;

use nkostadinov\user\helpers\Http;
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
 * @property string $register_ip
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
            ['email', 'required'],

            ['name', 'safe'],
        ];

        if(\Yii::$app->user->requireUsername === true)
            $rules[] = ['username', 'required'];

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
        $tokenModel = Token::findOne([
            'code' => $token,
            'type' => Token::TYPE_API_AUTH
        ]);
        if (!isset($tokenModel) or $tokenModel->isExpired)
            throw new UnauthorizedHttpException(Yii::t(Module::I18N_CATEGORY, 'Auth code not found or expired!'));

        return static::findOne($tokenModel->user_id);
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
     * Finds a user by id.
     *
     * @param integer $id The id of the user
     * @return User
     * @throws NotFoundHttpException If the user is not found
     */
    public static function findById($id)
    {
        $user = self::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found!');
        }

        return $user;
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

    /**
     * Confirms the registration of the user by the given token.
     *
     * @param Token $token
     * @return boolean True on success, false otherwise.
     */
    public function confirm($token)
    {
        return Yii::$app->user->confirmUser($this, $token);
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
        foreach ((new ReflectionClass(get_class()))->getConstants() as $name => $value) {
            if ($value == $this->status && strpos($name, 'STATUS_') === 0)
                return substr($name, 7, 255);
        }
    }

    public function getLastLoginText()
    {
        return Yii::$app->formatter->asDatetime($this->last_login) . "\n{$this->last_login_ip}";
    }

    /**
     * Deletes a user by email.
     *
     * NOTE: The user is not physically deleted, but is marked as unactive!
     *
     * @param string $email The email of the user that is to be deleted (marked as deleted)
     * @return boolean True on success, false otherwise
     * @throws NotFoundHttpException If the user is not found
     */
    public static function deleteByEmail($email)
    {
        $user = self::findByEmail($email);
        if (!$user) {
            throw new NotFoundHttpException(Yii::t(Module::I18N_CATEGORY, 'User not found!'));
        }

        $user->status = self::STATUS_DELETED;
        return $user->save(false);
    }

    /**
     * Deletes a user by id.
     *
     * NOTE: The user is not physically deleted, but is marked as unactive!
     *
     * @param integer $id The id of the user that is to be deleted (marked as deleted)
     * @return boolean True on success, false otherwise
     */
    public static function deleteById($id)
    {
        $user = self::findById($id);
        $user->status = self::STATUS_DELETED;
        return $user->save(false);
    }

    /**
     * Locks the user for Yii::$app->user->lockExpiration seconds.
     *
     * @return boolean True on success, false on failure.
     */
    public function lock()
    {
        return Yii::$app->user->lockUser($this);
    }

    /**
     * Unlocks the user.
     *
     * @return boolean True on success, false otherwise.
     */
    public function unlock()
    {
        return Yii::$app->user->unlockUser($this);
    }

    /**
     * Checks whether the user is locked.
     *
     * @return boolean True if locked, false otherwise.
     */
    public function getIsLocked()
    {
        return isset($this->locked_until) && $this->locked_until > time();
    }

    /**
     * Checks whether the user is active.
     * 
     * @return boolean True if active, false if deleted.
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Finds an active user by email or username.
     *
     * @param string $value The email or the username of the user.
     * @return User The User model if found.
     * @throws NotFoundHttpException If the user is not found.
     */
    public static function findByEmailOrUsername($value)
    {
        $user = self::find()
            ->where(['email' => $value])
            ->orWhere(['username' => $value])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->one();

        if (!$user) {
            throw new NotFoundHttpException(Yii::t(Module::I18N_CATEGORY, 'User not found!'));
        }

        return $user;
    }

    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->generateAuthKey();
            $this->register_ip = Http::getUserIP();
        }

        return parent::beforeSave($insert);
    }
}
