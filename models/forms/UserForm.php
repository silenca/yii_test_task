<?php

namespace app\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;
use app\models\Tag;
use yii\validators\EmailValidator;
use app\components\Filter;

/**
 * UserForm is the model behind the user form.
 */
class UserForm extends Model
{

    var $firstname;
    var $lastname;
    var $patronymic;
    var $role;
    var $int_id;
    var $edit_tags;
    var $tags_str;
    var $tags;
    var $password_sip;

    var $email;

    var $edited_id;

    public static function getAllCols() {
        return [
            'firstname',
            'lastname',
            'patronymic',
            'role',
            'email',
            'int_id',
            'password_sip',

        ];
    }

    public function rules()
    {
        return [
            [['firstname', 'lastname', 'patronymic'], 'requiredForUser'],
            [['email'], 'validateEmail'],
            [['email'], 'email'],
            [['tags_str'], 'tagsArray'],

            [['firstname', 'lastname', 'patronymic', 'password_sip'], 'string'],
            [['int_id', 'role'], 'integer']
        ];
    }

    public function tagsArray($attribute, $params)
    {
        $tags = array_map('trim', explode(',', $this->$attribute));
        foreach ($tags as $tag) {
            $this->checkTag($tag, $attribute);
            $tag_obj = Tag::getByName($tag);
            $this->tags[] = $tag_obj ?: (new Tag(['name' => $tag]));
        }
    }
    public function checkTag($tag, $attribute)
    {
        if (strlen($tag) > 150) {
            $this->addCustomError($attribute, 'Длина тега не должна превышать 150 символов');
        }
    }


    public function isUnique($value, $attr, $fields, $message_callback) {
        if ($value != null) {
            $or_where = ['or'];
            foreach ($fields as $field) {
                $or_where[] = [$field => $value];
            }
            $user = User::find()->where([])->andWhere($or_where);
            if ($this->edited_id) {
                $user->andWhere(['!=', 'id', $this->edited_id]);
            }
            $user = $user->one();
            if ($user) {
                $message_callback($attr, $value, $user->int_id);
                return $user->int_id;
            }
        }
        return true;
    }

    public function validateEmail()
    {
        $user = User::find()->asArray()->where(['email' => $this->email])->one();

        if (!empty($user) && $this->edit_tags == false && !$this->edited_id) {
            $this->addCustomError('email', 'Такой пользователь уже существует в системе.');
        }
    }

    public function attributeLabels()
    {
        return [
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'email' => 'Email',
            'password_sip' => 'Пароль Sip',
        ];
    }

    public function formName()
    {
        return 'user';
    }

    public function requiredForUser($attribute, $params)
    {
        if (empty($this->firstname) || empty($this->lastname) || empty($this->patronymic) || empty($this->email)) {
            $this->addCustomError($attribute, 'Необходимо заполнить ФИО и email');
        }
    }

    public function checkEmail($email, $attribute)
    {
        if ($email !== null) {
            $email_validator = new EmailValidator();
            if (!$email_validator->validate($email)) {
                $this->addCustomError($attribute, 'Email введен не верно');
            }
        }

    }

    public function addCustomError($attribute, $message = '')
    {
        if ($this->getFirstError($attribute) == null) {
            $this->addError($attribute, $message);
        }
    }

}
