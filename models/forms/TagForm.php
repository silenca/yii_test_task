<?php

namespace app\models\forms;

use app\models\Contact;
use app\models\Tag;
use Yii;
use yii\base\Model;
use yii\validators\EmailValidator;
use app\components\Filter;

/**
 * ContactForm is the model behind the contact form.
 */
class TagForm extends Model
{

    var $id;
    var $name;
    var $description;
    var $script;
    var $as_task;
    var $tag_users;
    var $start_date;
    var $end_date;

    var $edited_id;

    public static function getAllCols() {
        return [

        ];
    }

    public function __construct(array $config)
    {
        $this->end_date = date('Y-m-d H:i:s');
        parent::__construct($config);
    }

    /*
    'id',
    'name',
    'description',
    'script',
    'as_task',
    'tag_users',
    'start_date',
    'end_date',
    */

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'description', 'script'], 'string'],
            [['as_task'], 'integer'],
            [['start_date', 'end_date', 'as_task', 'tag_users'], 'safe'],
            [['start_date', 'end_date'], 'date', 'format' => 'yyyy-M-d H:m:s'],

//            [['name', 'surname', 'phones'], 'requiredForContact'],
//            [['phones'], 'phoneArray'],
//            [['emails'], 'emailArray'],
//
//            [['name', 'surname'], 'match', 'pattern' => "/^[\p{Cyrillic}\-]*$/u", 'message' => '{attribute} - Недопустимые символы'],
//            [['name', 'surname', 'middle_name'], 'string', 'length' => [1, 150],
//                'tooShort' => '{attribute} должен содержать больше {min} символов',
//                'tooLong' => '{attribute} должен содержать до {max} символов'],
//            [['middle_name'], 'match', 'pattern' => "/^[\p{Cyrillic}\-\s]*$/u", 'message' => 'Недопустимые символы'],
//            [['country'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\(\)]*$/u", 'message' => 'Недопустимые символы'],
//            [['region', 'area'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\(\)]*$/u", 'message' => 'Недопустимые символы'],
//            [['city'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\(\)\d]*$/u", 'message' => 'Недопустимые символы'],
//            [['street'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\d]*$/u", 'message' => 'Недопустимые символы'],
//            [['house', 'flat'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\d\/]*$/u", 'message' => 'Недопустимые символы'],
//
//            [['tags_str'], 'tagsArray'],
//
//            [[
//                'first_phone', 'second_phone', 'third_phone', 'fourth_phone',
//                'first_email', 'second_email',
//                'middle_name', 'region', 'area', 'city', 'street', 'house', 'flat'
//            ], 'default'],
        ];
    }

    public function attributeLabels()
    {
        return [

        ];
    }

    public function formName()
    {
        return 'tag_form';
    }

    public function addCustomError($attribute, $message = '')
    {
        if ($this->getFirstError($attribute) == null) {
            $this->addError($attribute, $message);
        }
    }

}
