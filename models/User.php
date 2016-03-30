<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface {

    const ROLE_ADMIN = 15;
    const ROLE_MANAGER = 5;
    const ROLE_OPERATOR = 1;

    public static function tableName() {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['firstname', 'lastname', 'patronymic', 'email', 'password_hash'], 'required'],
            [['notification_key'], 'string', 'max' => 32],
            [['email'], 'email'],
            [['email'], 'unique'],
        ];
    }

    public function attributeLabels() {
        return [
            'firstname' => 'имя',
            'lastname' => 'фамилия',
            'patronymic' => 'отчество',
            'email' => 'email',
            'password' => 'пароль'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token
        ]);
    }

    public static function findByEmail($email) {
        return static::findOne(['email' => $email]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    public static function getManagerIntIdsByIds($ids) {
        $ind_ids = [];
        if (count($ids) > 0) {
            $users = self::find()->select("int_id")->where(['id' => $ids,'role' => User::ROLE_MANAGER])->all();
            foreach ($users as $user) {
                $ind_ids[] = $user->int_id;
            }
        }
        return $ind_ids;
    }
    
    public static function getManagerByIntId($int_id) {
        return self::find()->where(['int_id' => $int_id,'role' => User::ROLE_MANAGER])->one();
    }

    public static function getManagersData($attr = null) {
        if ($attr == null) {
            return self::find()->where(['role' => User::ROLE_MANAGER])->all();
        } else {
            if (in_array($attr, array_values(self::attributes()))) {
                $attr_array = [];
                $users = self::find()->select($attr)->where(['role' => User::ROLE_MANAGER])->all();
                foreach ($users as $user) {
                    $attr_array[] = $user->$attr;
                }
                return $attr_array;
            }
            return false;
        }
    }

    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('user_tag', ['user_id' => 'id']);
    }

}
