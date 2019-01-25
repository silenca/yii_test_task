<?php

namespace app\controllers;

use app\models\SipChannel;
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

    const CALL_STATUS_ANSWERED = 'ANSWERED';
    const CALL_STATUS_NO_ANSWER = 'NO ANSWER';

    const PAYLOAD_JSON = 'json';

    protected $callStatusMap = [
        self::CALL_STATUS_ANSWERED => Call::CALL_STATUS_ANSWERED,
        self::CALL_STATUS_NO_ANSWER => Call::CALL_STATUS_MISSED,
    ];

    protected $typeToManagerKeyMap = [
        Call::TYPE_INCOMING => 'answered',
        Call::TYPE_OUTCOMING => 'callerid',
    ];

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

    public function actionGetmanagerbycallerid()
    {
        $request = $this->getPayload();

        try {
            $callerPhone = $request['callerid'] ?? null;
            if(Filter::isPositiveNumber($callerPhone) && Filter::length($callerPhone, 4, 15)) {
                $data = [
                    'responsible' => '',
                    'free' => User::getAvailableIntIds(),
                ];
                $contact = Contact::getContactByPhone($callerPhone);
                if($contact && $contact->manager) {
                    $data['responsible'] = (string) $contact->manager->int_id;
                }

                return $this->json($data);
            } else {
                throw new \Exception('Invalid caller data');
            }
        } catch(\Exception $e) {
            return $this->json([], $e->getCode() ?? 415, ['error' => $e->getMessage()]);
        }
    }

    public function actionAnsweredoperator()
    {
        $request = $this->getPayload();
        try {
            $call = Call::getByUniquelId($request['uniqueid'] ?? 0);
            if(!$call) {
                throw new \Exception('Can not find call with ID#'.$request['uniqueid']);
            }

            $manager = User::getManagerByIntId($request['answered'] ?? 0);
            if(!$manager) {
                throw new \Exception('Can not find manager with INT_ID: '.$request['answered']);
            }

            $call->assignManager($manager->id);

            return $this->json([], 200);
        } catch(\Exception $e) {
            return $this->json([], $e->getCode() || 400, explode('::', $e->getMessage()));
        }
    }

    /**
     * @throws \Exception
     *
     * @TODO Delete method because it seems like it is never used
     */
    public function actionSendIncomingCall()
    {
        $phone = Yii::$app->request->post('phone');
        $user_id = Yii::$app->user->identity->id;
        $call_order_script = Yii::$app->params['call_order_script'];
        $contact_id = Yii::$app->request->post('contact_id');


        $query = new Query();
        $query->select('`temp_contacts_pool`.`manager_id`')->from('`call`')
            ->join('LEFT JOIN', '`temp_contacts_pool`', '`temp_contacts_pool`.`order_token` = `call`.`call_order_token`')
            ->where(['`call`.`status`' => 'new'])
            ->andWhere(['is not','`temp_contacts_pool`.`order_token`', null]);


        /**
         SELECT `temp_contacts_pool`.`manager_id`, `call`.* FROM `call`
         LEFT JOIN `temp_contacts_pool` on `temp_contacts_pool`.`order_token` = `call`.`call_order_token`
         WHERE `status` = 'new' and `temp_contacts_pool`.`order_token` is not null
         */
        
        $calls = $query->all();
        $canCall = true;
        foreach ($calls as $call) {
            if ($call['manager_id'] == $user_id)
                $canCall = false;
        }
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

            //TODO Temp
            $logMessage = date('d-m-Y G:i:s') .' Исходящий звоноу.Номер телефона: ' .$phone .', оператор: '.$user_id.', token: '.$call_order_token.', тег: '. $tag_id;
            $logMessage .= "\r\n===============================================================". "\r\n\r\n";
            file_put_contents(Yii::getAlias('@runtime_log_folder') . '/call.log', $logMessage, FILE_APPEND);

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

    public function actionCallstart()
    {
        $form = new CallForm();
        try {
            $request = json_decode(file_get_contents('php://input'), true);

            if (!is_array($request) || empty($request)) {
                throw new \Exception('Invalid request data', 400);
            }

            $form->scenario = CallForm::SCENARIO_CALLSTART;
            $form->load($request);

            if(!$form->validate()) {
                throw new \Exception(implode('::', $form->getErrors()), 415);
            }

            $callerNumber = $request['callerid'];
            $answeredNumber = $request['answered'];
            $callId = $request['uniqueid'];

            $callData = [];
            $contact = null;

            switch(true) {
                case Call::isIncomingCall($callerNumber, $answeredNumber):
                    $contact = Contact::getContactByPhone($callerNumber);

                    $callData = [
                        'type' => Call::TYPE_INCOMING,
                        'phone_number' => $callerNumber,
                    ];
                    break;
                case Call::isOutcomingCall($callerNumber, $answeredNumber):
                    $contact = Contact::getContactByPhone($answeredNumber);

                    $callOrderToken = $request['call_order_token'] ?? 'None';
                    if(strtoupper($callOrderToken) == 'NONE') {
                        $callOrderToken = null;
                    }
                    // "tag_id" processing was removed
                    $callData = [
                        'type' => Call::TYPE_OUTCOMING,
                        'phone_number' => $answeredNumber,
                        'call_order_token' => $callOrderToken,
                    ];
                    break;
            }

            if(!empty($callData)) {
                $call = new Call();
                $call->setAttributes([
                    'unique_id' => $callId,
                    'date_time' => date(Call::DATE_TIME_FORMAT),
                    'contact_id' => null,
                    'status' => Call::CALL_STATUS_NEW,
                    'sip_channel_id' => $form->sip_channel,
                    'sended_crm' => 1,
                ]);

                if($contact) {
                    $callData['contact_id'] = $contact->id;
                    $callData['sender_crm'] = 0;
                }

                $call->setAttributes($callData);
                $call->save();
            }

            return $this->json([]);
        } catch(\Exception $e) {
            return $this->json([], $e->getCode() ?? 400, explode('::', $e->getMessage()));
        }
    }

    public function actionCallend()
    {
        $form = new CallForm();
        $form->scenario = CallForm::SCENARIO_CALLEND;

        try {
            $request = json_decode(file_get_contents('php://input'), true);
            $form->load($request);
            if(!$form->validate()) {
                throw new \Exception(implode('::', $form->getErrors()), 415);
            }

            $call = Call::getByUniquelId($request['uniqueid'] ?? '');
            if(!$call) {
                throw new \Exception('Can not find call with ID: '.$request['uniqueid'], 404);
            }

            $call->setAttributes([
                'date_time' => date(Call::DATE_TIME_FORMAT, strtotime($request['datetime'])),
                'total_time' => intval($request['totaltime'] ?? 0),
                'answered_time' => intval($request['answeredtime'] ?? 0),
                'record' => $request['record_file'],
                'status' => $this->callStatusMap[$request['status']] ?? Call::CALL_STATUS_FAILURE,
            ]);

            // Update CallManager relation and create notifications for managers
            if(($request['status'] ?? '') === self::CALL_STATUS_NO_ANSWER) {
                $intIds = array_map(
                    'trim',
                    explode(',', $this->typeToManagerKeyMap[$call->type] ?? '')
                );
                $managers = User::find()->where(['int_id' => $intIds])->all();
                foreach($managers as $manager) {
                    // Create notification
                    (new ManagerNotification())->add(
                        $call->date_time,
                        'call_missed',
                        $manager->id,
                        $call->phone_number,
                        $call->contact_id
                    );
                    // Assign call to manager
                    $call->assignManager($manager->id);
                    // Register missed call
                    (new MissedCall())->add($call->id, $manager->id);
                }
            }

            $call->save();

            return $this->json([]);
        } catch(\Exception $e) {
            return $this->json([], $e->getCode() ?? 500, explode('::', $e->getMessage()));
        }
    }

    protected function getPayload($type = self::PAYLOAD_JSON)
    {
        $content = file_get_contents('php://input');
        switch($type) {
            case self::PAYLOAD_JSON:
                return json_decode($content, true);
            default:
                return $content;
        }
    }
}
