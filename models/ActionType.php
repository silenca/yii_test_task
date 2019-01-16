<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "action_type".
 *
 * @property integer $id
 * @property string $name
 */
class ActionType extends \yii\db\ActiveRecord
{
    private $_label;

//    public function __construct()
//    {
//        parent::__construct();
//
//        $this->label = $this->getLabel($this->name);
//    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'action_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    public function setLabel($value)
    {
        $this->_label = $value;
    }

    public function getLabel()
    {
        $labels = [
            'scheduled_call' => 'Запланированный исходящий звонок',
            'scheduled_email' => 'Запланированное Email сообщение',
            'ring_round' => 'Прозвон контакта',
            'scheduled_visit' => 'Запланированный визит',
        ];
        if ($this->_label === null) {
            $this->setLabel($labels[$this->name]);
        }

        return $this->_label;
    }
}
