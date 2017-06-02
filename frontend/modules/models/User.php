<?php

namespace frontend\modules\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property integer $group_id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $access_token
 */
class User extends ActiveRecord implements IdentityInterface
{

	const STATUS_DELETED = 0;
	const STATUS_ACTIVE = 10;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'first_name', 'last_name', 'username', 'patronymic', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'], 'required'],
            [['group_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['first_name', 'last_name', 'patronymic', 'username', 'password_hash', 'password_reset_token', 'email', 'address'], 'string', 'max' => 255],
            [['auth_key', 'access_token'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
	        [['phone'], 'string', 'max' => 20],
            [['id'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	public function can( $perm ) {
		$query = new Query();
		$result = $query
			->select(['item_name'])
			->from('auth_assignment')
			->where(['item_name' => $perm, 'user_id' => $this->id])
			->one();
		return $result;
    }

    public function save( $runValidation = true, $attributeNames = null ) {

    	$this->generateAuthKey();
    	$this->generatePasswordResetToken();
	    $this->generateAccessToken();
    	$this->status = 10;

    	if ($this->isNewRecord) {
		    $this->created_at = time();
	    }

    	$this->updated_at = time();

	    return parent::save( $runValidation, $attributeNames ); // TODO: Change the autogenerated stub
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

	public function generateAccessToken() {
		$this->access_token = Yii::$app->security->generateRandomString();
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

    public function updateUser($ph)
    {
        Yii::trace(json_encode(['hello']));
        $this->updated_at = time();

        if ($this->password_hash) {
            Yii::trace($this->password_hash);
            $this->setPassword($this->password_hash);
        } else {
            Yii::trace($this->password_hash);
            $this->password_hash = $ph;
        }


        return $this->save();
    }

    public function saveUser()
    {
        $this->created_at = time();
        $this->updated_at = time();
        $this->generateAuthKey();
        $this->generatePasswordResetToken();
        if ($this->password_hash != '')
            $this->setPassword($this->password_hash);
        return $this->save();
    }

	public function validatePassword($password)
	{
		return Yii::$app->security->validatePassword($password, $this->password_hash);
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
		Yii::trace($token);
		return static::findOne(['access_token' => $token]);
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return static|null
	 */
	public static function findByUsername($username)
	{
		return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
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
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
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
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

	public function getRoles() {
		return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }


}
