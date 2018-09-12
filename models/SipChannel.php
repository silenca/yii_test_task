<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sip_channel".
 *
 * @property integer $id
 * @property string $phone_number
 * @property string $host
 * @property integer $port
 * @property string $login
 * @property string $password
 */
class SipChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sip_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone_number', 'host', 'port', 'login', 'password'], 'required'],
            [['port'], 'integer'],
            [['phone_number'], 'string', 'max' => 20],
            [['host', 'login', 'password'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone_number' => 'Phone Number',
            'host' => 'Host',
            'port' => 'Port',
            'login' => 'Login',
            'password' => 'Password',
        ];
    }

    public static $safe_fields = [
        'int_id',
        'phone_number',
        'host',
        'port',
        'login',
        'password'
    ];

    public static function getColsForTableView()
    {
        $result = [
            'id' => ['label' => 'ID', 'have_search' => false, 'orderable' => true],
            'phone_number' => ['label' => 'Номер телефона', 'have_search' => true, 'orderable' => true],
            'host' => ['label' => 'Хост', 'have_search' => true, 'orderable' => true],
            'port' => ['label' => 'Порт', 'have_search' => false, 'orderable' => false],
            'login' => ['label' => 'Логин', 'have_search' => true, 'orderable' => true],
            'password' => ['label' => 'Пароль', 'have_search' => false, 'orderable' => false],
            'delete_button' => ['label' => 'Удалить', 'have_search' => false]
        ];
        return $result;
    }

    public static function deleteById($id) {
        $channel = self::find()->where(['id' =>(int) $id])->one();
        if ($channel) {
            return $channel->delete();
        }
        return false;
    }
}
