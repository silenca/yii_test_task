<?php
/**
 * Created by PhpStorm.
 * User: ahwd
 * Date: 11/5/18
 * Time: 12:38 PM
 */

namespace app\controllers\api;

use app\models\Contact;
use yii\data\ActiveDataProvider;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\Response;
class ContactsController extends ActiveController
{
    public $modelClass = 'app\models\Contact';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['class'] = QueryParamAuth::className();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_XML;
        return $behaviors;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => self::fields(),
        ]);
    }

    public function fields()
    {
        if (!empty($this->birthday)) {
            $birthday = \DateTime::createFromFormat('Y-m-d', $this->birthday);
            if ($birthday) {
                $birthday = $birthday->format('Y-m-d\TH:i:s');
            } else {
                $birthday = "";
            }
        } else {
            $birthday = "";
        }
        /** @var Contact $this */
        return [
            'oid' => $this->medium_oid,
            'E-mail' => $this->first_email,
            'name' => function () {
                return $this->surname . ' ' . $this->name . ' ' . $this->middle_name;
            },
            'ТелефонМоб' => $this->first_phone,
            'ДатаРождения' => $birthday,
            'ИсточникИнфомации' => $this->getAttractionChannel(),
        ];
    }

    public function actions()
    {
        $parent = parent::actions();
        unset($parent['create']);
        return $parent;
    }
}