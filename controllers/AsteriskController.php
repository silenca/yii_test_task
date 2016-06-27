<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Contact;
use app\models\User;
use app\models\Call;
use app\models\TempContactsPool;
use app\components\SessionHelper;
use app\components\Filter;
use app\components\Notification;
use app\models\forms\CallForm;
use yii\helpers\BaseJson;

class AsteriskController extends BaseController {

    public $enableCsrfValidation = false;

    const INT_ID_LENGTH = 3;

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
//                        'ips' => ['127.0.0.1'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'getmanagerbycallerid' => ['put'],
                    'answeredoperator' => ['put'],
                    'callstart' => ['put'],
                    'callend' => ['put'],
                    'send-incoming-call' => ['post']
                ],
            ],
        ];
    }

    public function actionGetmanagerbycallerid() {
        $json = file_get_contents('php://input');
        $post = json_decode($json, true);
        $caller_phone = $post['callerid'];
        $call_uniqueid = $post['uniqueid'];
        if (Filter::isPositiveNumber($caller_phone) && Filter::length($caller_phone, 4, 15)) {
            $contact = Contact::getContactByPhone($caller_phone);
            $manager_int_id = null;
            $contact_id = null;
            if ($contact) {
                $manager = $contact->manager;
                $manager_int_id = $manager->int_id;
                $contact_id = $contact->id;
            }
            $online_user_ids = SessionHelper::getOnlineUserIds();
            $online_users = User::getManagerIntIdsByIds($online_user_ids);
            $response = [];
            if ($manager_int_id) {
                $response['responsible'] = strval($manager_int_id);
            }
            $response['free'] = implode(',', $online_users);

            $this->json($response, 200);
        } else {
            $this->json(false, 415, ['callerid' => 'Incorrect data']);
        }
    }

    public function actionAnsweredoperator() {
        $this->json([], 200);
//        $caller_phone = Yii::$app->request->post('callerid');
//        $answered_id = Yii::$app->request->post('answered');
//        $call_uniqueid = Yii::$app->request->get('uniqueid');
//        if (Filter::isPositiveNumber($caller_phone) && Filter::isPositiveNumber($answered_id)) {
//            
//        } else {
//            $this->json([], 415, ['callerid' => 'Не корректные данные']);
//        }
    }

    public function actionSendIncomingCall()
    {
        $phone = Yii::$app->request->post('phone');
        $user_id = Yii::$app->user->identity->id;
        $call_order_script = Yii::$app->params['call_order_script'];
        $contact_id = Yii::$app->request->post('contact_id');

        /**
        SELECT `t`.`manager_id` FROM `call`
        LEFT JOIN `temp_contacts_pool` as `t` ON  `t`.`order_token` = `call`.`call_order_token`
        WHERE (`call`.`status`='new')  AND `t`.`order_token` is not null
         */



        $query = new Query();
        $query->select('`temp_contacts_pool`.`manager_id`')->from('`call`')
            ->join('LEFT JOIN', '`temp_contacts_pool`', '`temp_contacts_pool`.`order_token` = `call`.`call_order_token`')
            ->where(['`call`.`status`' => 'new'])
            ->andWhere(['is not','`temp_contacts_pool`.`order_token`', null]);
        $calls = $query->all();
        $canCall = true;
        foreach ($calls as $call) {
            if ($call['manager_id'] == $user_id)
                $canCall = false;
        }
//        $cont_pool = TempContactsPool::find()
//            ->where(['contact_id' => $contact_id, 'manager_id' => $user_id, 'tag_id' => $tag_id])
//            ->andWhere(['is not','order_token', null])->one();
        if (!$canCall) {
            $this->json([], 423);
        }
        if (file_exists($call_order_script)) {
            require_once $call_order_script;
            $user_int_id = Yii::$app->user->identity->int_id;
            $tag_id = Yii::$app->request->post('tag_id');

            $call_order_token = time().$user_id;
            $options = array("external" => $phone, "internal" => $user_int_id, "call_order_token" => $call_order_token, 'tag_id' => $tag_id);


            //file_put_contents('/var/log/pool.log', 'SendIncomingCall : ' . $contact_id .' : ' . $tag_id . ':'. Yii::$app->user->identity->role . PHP_EOL, FILE_APPEND);

            $res = call_order($options);
            if ($res[0] == true) {
                if ($contact_id && $tag_id) {
                    Contact::addContInPool($contact_id, $user_id, $tag_id, $call_order_token);
                }
                $this->json(['call_order_token' => $call_order_token,
                    'contact_id' => $contact_id,
                    'user_id' => $user_id,
                    'tag_id' => $tag_id
                ], 200);
            } else {
                $this->json([], 415);
            }
        }
        $this->json([], 500);
    }

    public function actionCallstart() {
        $call_form = new CallForm();
        $json = file_get_contents('php://input');
        $post = json_decode($json, true);
        $call_form->scenario = CallForm::SCENARIO_CALLSTART;
        $call_form->load($post);
        if ($call_form->validate()) {
            $callerid = $post['callerid'];
            $answered = $post['answered'];
            $call_uniqueid = $post['uniqueid'];
            $manager_int_id = null;
            $contact_id = null;
            $call = new Call();
            if (strlen($callerid) !== self::INT_ID_LENGTH && strlen($answered) !== self::INT_ID_LENGTH) {
                //Входящий звонок
                if (substr($callerid, 0, 1) == '+') {
                    $callerid = substr($callerid, 1);
                }
                $contact = Contact::getContactByPhone($callerid);
                $request_params['phone'] = $callerid;
                if ($contact) {
                    $contact_id = $contact->id;
                    $request_params['id'] = $contact_id;
                    $request_params['contact_name'] = implode(' ', array_filter([$contact->surname, $contact->name, $contact->middle_name]));
                }
                Notification::incomingCall($request_params);
                $call->outgoing($call_uniqueid, $contact_id, $callerid);
            } else if (strlen($callerid) == self::INT_ID_LENGTH && strlen($answered) == self::INT_ID_LENGTH) {
                //Внутренний звонок
                $call->incoming($call_uniqueid, $contact_id, $answered, null, null);
            } else {
                //Исходящий
                $contact = Contact::getContactByPhone($answered);
                $manager_int_id = $callerid;
                if ($contact) {
                    $contact_id = $contact->id;
                }
                $call_order_token = (isset($post['call_order_token']) && $post['call_order_token'] !== $call_uniqueid) ? $post['call_order_token'] : null;
                $tag_id = (isset($post['tag_id']) && $post['tag_id'] !== 'NONE') ? $post['tag_id'] : null;
                $call->incoming($call_uniqueid, $contact_id, $answered, $call_order_token, $tag_id);
            }
            $this->json([], 200);
        } else {
            $this->json([], 415, $call_form->getErrors());
        }
    }

    public function actionCallend() {
        $call_form = new CallForm();
        try {
            $json = file_get_contents('php://input');
            $post = json_decode($json, true);
            $call_form->scenario = CallForm::SCENARIO_CALLEND;
            $call_form->load($post);
            if ($call_form->validate()) {
                $uniqueid = $post['uniqueid'];
                $call = Call::getByUniquelId($uniqueid);
                if ($call) {
                    $date_time = $post['datetime'];
                    $status = $post['status'];
                    if ($call->type == Call::CALL_INCOMING) {
                        //Исходящий
                        $managers = $post['callerid'];
                    } else {
                        //Входящий
                        $managers = $post['answered'];
                    }
                    $total_time = null;
                    $answered_time = null;
                    $record_file = null;
                    if ($status == "ANSWERED") {
                        $total_time = $post['totaltime'];
                        $answered_time = $post['answeredtime'];
                        $record_file = $post['record_file'];
                    }

                    if (isset($call->contact_id)) {
                        if ($cont_pool = TempContactsPool::findOne(['order_token' => $call->call_order_token])) {
                            $call->tag_id = $cont_pool->tag_id;
                            $cont_pool->delete();
                        }
                    }
                    //file_put_contents('/var/log/pool.log', 'callEnd : ' . $call->call_order_token .' : ' . $call->tag_id . PHP_EOL, FILE_APPEND);
                    if ($call->callEnd($date_time, $total_time, $answered_time, $record_file, $status, $managers)) {
                        $this->json([], 200);
                    }

                    $this->json([], 500);
                } else {
                    $this->json([], 415, ['uniqueid' => 'Not found']);
                }
            } else {
                $this->json([], 415, $call_form->getErrors());
            }
        } catch (\Exception $ex) {
            $this->json([], 500);
        }
    }

    public function actionTest() {
        $call = Call::getByUniquelId('1464945097.69');
        $manager = User::find()->where(['id' => 3])->one();
        $call->sendToCRM($manager);
    }

}
