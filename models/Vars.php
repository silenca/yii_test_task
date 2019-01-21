<?php

namespace app\models;


use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "vars".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property string $value
 */
class Vars extends ActiveRecord
{
    const TYPE_INT = 1;
    const TYPE_STRING = 2;
    const TYPE_TEXT = 3;
    const TYPE_ARRAY = 4;

    public function attributeLabels()
    {
        return [
            'type' => 'Type',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }

    public static function tableName()
    {
        return '{{vars}}';
    }

    public static function get($name, $default = null)
    {
        $var = self::findOne(['name' => $name]);
        if(!$var) {
            return $default;
        } else {
            return self::parseVar($var);
        }
    }

    /**
     * @param $name
     * @param $value
     * @param int $type
     * @return Vars|null
     */
    public static function set($name, $value, $type = self::TYPE_TEXT)
    {
        $var = self::findOne(['name' => $name]);
        if(!$var) {
            $var = new Vars();
            $var->name = $name;
            $var->type = $type;
        }

        $var->value = $value;

        $var->save();
        return $var;
    }

    protected static function parseVar(Vars $var)
    {
        switch($var->type) {
            case self::TYPE_INT:
                return (int) $var->value;
            case self::TYPE_STRING:
            case self::TYPE_TEXT:
                return (string) $var->value;
            case self::TYPE_ARRAY:
                return json_decode($var->value, true);
            default:
                throw new \Exception('Invalid variable type');
        }
    }

    public function typeName()
    {
        switch($this->type) {
            case self::TYPE_INT:
                return 'Numeric';
            case self::TYPE_STRING:
                return 'String';
            case self::TYPE_TEXT:
                return 'Text';
            case self::TYPE_ARRAY:
                return 'Array';
            default:
                return 'Unknown';
        }
    }
}