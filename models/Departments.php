<?php

namespace app\models;

/**
 * This is the model class for table "departments".
 *
 * @property integer $id
 * @property string $title
 * @property string $api_url
 * @property string $api_send_url
 */
class Departments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'departments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'api_url'], 'required'],
            [['title'], 'string', 'max' => 50],
            [['api_url'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'api_url' => 'Api Url',
        ];
    }
}
