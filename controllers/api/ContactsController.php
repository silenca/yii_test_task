<?php
/**
 * Created by PhpStorm.
 * User: ahwd
 * Date: 11/5/18
 * Time: 12:38 PM
 */
namespace app\controllers\api;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\Response;
use app\models\Contact;

class ContactsController extends ActiveController
{
    public $modelClass = 'app\models\Contact';

    public function fields()
    {
        return [
            'oid' => $this->medium_oid,
            'E-mail' => $this->first_email,
            'name' => function () {
                return $this->name . ' ' . $this->surname . ' ' . $this->middle_name;
            },
            'ТелефонМоб' => $this->first_phone,
            'ДатаРождения' => $this->birthday . 'T00:00:00.0',
            'ИсточникИнфомации' => $this->getAttractionChannel(),
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_XML;
        return $behaviors;
    }

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => self::fields(),
        ]);
    }

}