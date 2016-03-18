<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 */
class Tag extends \yii\db\ActiveRecord {

    public static $safe_fields = [
        'name',
        'description',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'description'], 'string'],
            [['name'], 'required']
        ];
    }

    public static function getTableColumns() {
        return [
            1 => 'name',
            2 => 'description',
        ];
    }

    public function getUsers() {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('user_tag', ['tag_id' => 'id']);
    }

    public function getContacts() {
        return $this->hasMany(Contact::className(), ['id' => 'contact_id'])->viaTable('contact_tag', ['tag_id' => 'id']);
    }

    public static function getById($id) {
        return self::find()->where(['id' => $id])->one();
    }

    public function edit() {

    }
}