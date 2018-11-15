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
        if(!empty($this->birthday)) {
            $birthday = \DateTime::createFromFormat('Y-m-d',$this->birthday);
            if($birthday) {
                $birthday = $birthday->format('Y-m-d\TH:i:s.0');
            } else {
                $birthday ="";
            }
        } else {
            $birthday ="";
        }
        return [
            'oid' => $this->medium_oid,
            'E-mail' => $this->first_email,
            'name' => function () {
                return $this->surname  . ' ' . $this->name . ' ' . $this->middle_name;
            },
            'ТелефонМоб' => $this->first_phone,
            'ДатаРождения' => $birthday,
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

    public function actionCreate()
    {
        $content = \Yii::$app->request->getRawBody();
        $xmlParser = xml_parser_create();
        xml_parse_into_struct($xmlParser, $content, $array, $index);
        $contacts = [];
        foreach ($array as $contact) {
            if ($contact['attributes']['OID']) {
               $contacts[] = \app\controllers\ContactsController::actionSaveContacts($contact);
            }

        }
        return json_encode($contacts);
    }

    public function actions()
    {
        $parent = parent::actions();
        unset($parent['create']);
        return $parent;
    }
}