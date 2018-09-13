<?php
/**
 * SipChannelForm.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\models\forms;


use yii\base\Model;

class SipChannelForm extends Model
{
    var $phone_number;
    var $host;
    var $port;
    var $login;
    var $password;
    var $edited_id;

    public function rules()
    {
        return [
            [['phone_number','host','port','login','password'], 'required', 'message' => 'Необходимо заполнить {attribute}'],
            [['phone_number'],'match', 'pattern' => "/^\+?\d*$/", 'message' => 'Ошибка: в поле {attribute} - Недопустимые символы'],
            [['port'],'match', 'pattern' => "/^\d*$/", 'message' => 'Ошибка: в поле {attribute} - Недопустимые символы'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'phone_number' => 'Номер телефона',
            'host' => 'Хост',
            'port' => 'Порт',
            'login' => 'Логин',
            'password' => 'Пароль',
        ];
    }
}