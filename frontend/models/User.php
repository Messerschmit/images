<?php
namespace frontend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\redis;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $about
 * @property integer $type
 * @property string $nickname
 * @property string $picture
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
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
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
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

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function getNickname()
    {
        return ($this->nickname) ? $this->nickname : $this->id;
    }
    
    /*
     * Subscribe current User to given User
     * @param \frontend\models\User $user
     */
    
    public function followUser(User $user)
    {
        $k1 = "user:{$this->getId()}:subscriptions";
        $k2 = "user:{$user->getId()}:followers";
        
        /* @var $redis Connection */
        $redis = Yii::$app->redis;
        
        $redis->sadd($k1, $user->getId());
        $redis->sadd($k2, $this->getId());
        
    }
    
    /*
     * Subscribe current User to given User
     * @param \frontend\models\User $user
     */
    public function unfollowUser(User $user) {
        
       $k1 = "user:{$this->getId()}:subscriptions"; 
       $k2 = "user:{$user->getId()}:followers";
       
       /* @var $redis Connection */
       $redis = Yii::$app->redis;
       
       $redis->srem($k1, $user->getId());
       $redis->srem($k2, $this->getId());
    }
    
    public function getSubscriptions(){
        
        /* @var $redis Connection */
        
        $redis = Yii::$app->redis;
        
        $key = "user:{$this->getId()}:subscriptions";
        
        $ids = $redis->smembers($key);
        
        return User::find()->select('id, username, nickname')->where(['id' => $ids])->orderBy('username')->asArray()->all();
    }
    
    public function getFollowers() {
     
        /* @var $redis Connection */
        
        $redis = Yii::$app->redis;
        
        $key = "user:{$this->getId()}:followers";
        
        $ids = $redis->smembers($key);
        
        return User::find()->select('id, username, nickname')->where(['id' => $ids])->orderBy('username')->asArray()->all();
    }
    
    /*
     * @return mixed
     */
    public function countSubscriptions() {
    
        $redis = Yii::$app->redis;
        
        return $redis->scard("user:{$this->getId()}:subscriptions");
        
    }
    
    /*
     * @return mixed
     */
    public function countFollowers() {
        
        $redis = Yii::$app->redis;
        
        return $redis->scard("user:{$this->getId()}:followers");
    }


    /*

     * Find Mutual users in current User and given User
     * @var /frontend/models/User
     * @return array
     */
    public function getMutualSubscriptionsTo(User $user) {
        
        // Current user subscriptions
        $key1 = "user:{$this->getId()}:subscriptions";
        // Given user followers
        $key2 = "user:{$user->getId()}:followers";
        
       /* @var $redis Connection */
        $redis = Yii::$app->redis;
        
        $ids = $redis->sinter($key1, $key2);
        return User::find()->select('id, username, nickname')->where(['id' => $ids])->orderBy('username')->asArray()->all();
    }
}
