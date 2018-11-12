<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property mixed role
 */
class User extends ActiveRecord implements IdentityInterface {

     const ROLE_ADMIN = 15;
     const ROLE_SUPERVISOR = 10;
     const ROLE_MANAGER = 5;
     const ROLE_OPERATOR = 1;
    public $id;

    public static function tableName() {
        return 'user';
    }

    public $remove_tags;
 
    public static $safe_fields = [
        'int_id',
        'firstname',
        'lastname',
        'patronymic',
        'email',
        'role',
        'is_deleted',
        'settings',
    ];

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
            [['int_id', 'role'], 'integer'],
            [['notification_key'], 'string', 'max' => 32],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['is_deleted', 'settings','filter_config','cols_config'], 'safe']
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

    public function getUserRole()
    {
        $user_role = '';
        if (Yii::$app->user->can('admin')) {
            $user_role = 'admin';
        } elseif (Yii::$app->user->can('manager')) {
            $user_role = 'manager';
        } elseif (Yii::$app->user->can('operator')) {
            $user_role = 'operator';
        } elseif (Yii::$app->user->can('supervisor')) {
            $user_role = 'supervisor';
        }
        return $user_role;
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
        $use_archived_tags = false;
        if (Yii::$app->user->identity) {
            $use_archived_tags = Yii::$app->user->identity->getSetting('use_archive_tags');
        }

        if ($use_archived_tags) {
            return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
                ->viaTable('user_tag', ['user_id' => 'id']);
        }
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->andOnCondition(['`tag`.`is_deleted`' => 0])
            ->viaTable('user_tag', ['user_id' => 'id']);

    }

    public static function getColsForTableView() {
        return [
            'id' => ['label' => 'ID', 'have_search' => false],
            'name' => ['label' => 'ФИО', 'have_search' => true, 'db_cols' => ['firstname', 'lastname', 'patronymic']],
            'role' => ['label' => 'Роль', 'have_search' => false],
            'int_id' => ['label' => '№', 'have_search' => false,],
            'email' => ['label' => 'Email', 'have_search' => true],
            'tags' => ['label' => 'Теги', 'have_search' => true],
            'delete_button' => ['label' => 'Удалить', 'have_search' => false]
        ];
    }

    public function edit($related) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save();
            if (isset($related['tags'])) {
                $this->tags = $related['tags'];
            }
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    public static function deleteById($id) {
        $user = self::find()->where(['id' => $id])->one();
        if ($user) {
            $user->is_deleted = 1;
            return $user->save();
        }
        return false;
    }

    public function setTags($new_tags) {
        if ($this->remove_tags == true) {
            $this->unlinkAll('tags');
        }

        foreach ($new_tags as $new_tag) {
            $new_tag->save();

            $user_has_tag = UserTag::find()->where(['user_id' => $this->id, 'tag_id' => $new_tag->id])->exists();

            if ($user_has_tag == false) {
                $this->link('tags', $new_tag);
            }
        }
    }

    public function getSettings() {
        return unserialize($this->settings);
    }

    public function getSetting($name) {
        $settings = unserialize($this->settings);
        return $settings[$name];
    }

    public function setSetting($name, $value) {
        $settings = $this->getSettings();
        $settings[$name] = $value;
        $this->settings = serialize($settings);
        return $this->save();
    }

}
