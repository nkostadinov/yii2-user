<?php
namespace nkostadinov\user\models;

use common\models\UserAccount;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;

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
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['email', 'email'],
            [['email'], 'required', 'on' => 'register']
        ];
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
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
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

    public function register()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        if (Yii::$app->user->enableConfirmation == false) {
            $this->confirmed_on = time();
        }
//        if ($this->module->enableGeneratingPassword) {
//            $this->password = Password::generate(8);
//        }
//        $this->trigger(self::USER_REGISTER_INIT);
        if ($this->save()) {
//            $this->trigger(self::USER_REGISTER_DONE);
            if (Yii::$app->user->enableConfirmation) {
                $token = \Yii::createObject([
                    'class' => Token::className(),
                    'type' => Token::TYPE_CONFIRMATION,
                ]);
                $token->link('user', $this);
                Yii::$app->user->notificator->sendConfirmationMessage($this, $token);
            } else {
                \Yii::$app->user->login($this);
            }
//            if ($this->module->enableGeneratingPassword) {
//                $this->mailer->sendWelcomeMessage($this);
//            }
//            \Yii::$app->session->setFlash('info', $this->getFlashMessage());
//            \Yii::getLogger()->log('User has been registered', Logger::LEVEL_INFO);
            return true;
        }
//        \Yii::getLogger()->log('An error occurred while registering user account', Logger::LEVEL_ERROR);
        return false;

    }

    public function confirm($code)
    {
        $token = Token::findOne([
            'code' => $code,
            'type' => Token::TYPE_CONFIRMATION,
            'user_id' => $this->id,
        ]);

        if (!isset($token) or $token->isExpired)
            throw new NotFoundHttpException("Confirmation code not found or expired!");
        else {
            $token->delete();
            $this->confirmed_on = time();
            return $this->save(false);
        }
    }

    public function getIsConfirmed()
    {
        return isset($this->confirmed_on);
    }
}
