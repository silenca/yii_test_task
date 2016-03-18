<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\ContactContract;
use yii\db\Query;
use app\models\Contact;
use app\models\ContactComment;
use app\models\User;
use app\models\ObjectApartment;
use app\models\ContractSolution;
use app\models\ContractPayment;
use app\models\forms\SolutionForm;
use app\models\forms\PaymentForm;
use app\components\widgets\ContractTableWidget;

class ContractsController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata', 'solution',],
                        'allow' => true,
                        'roles' => ['fin_dir'],
                    ],
                    [
                        'actions' => ['get-by-contact-id'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['payment', 'get-details-by-contact-id'],
                        'allow' => true,
                        'roles' => ['manager', 'supervisor', 'admin'],
                    ],
                    [
                        'actions' => ['get-payments', 'get-payments-by-contact-id', 'comment', 'download'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                    $id = Yii::$app->request->get('id');
                    $user_id = Yii::$app->user->identity->id;
                    if (Yii::$app->user->identity->role === User::ROLE_FIN_DIR || Yii::$app->user->identity->role == User::ROLE_SUPERVISOR)
                        return true;
                    $contact = Contact::find()->where(['id' => $id, 'manager_id' => $user_id])->one();
                    if ($contact)
                        return true;
                    return false;
                }
                    ]
                ],
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $total_count = ContactContract::find()->count();
        $columns = ContactContract::getTableColumns();
        $query = new Query();
        $query->select($columns)
                ->from(ContactContract::tableName() . ' as `cc`')
                ->join('LEFT JOIN', Contact::tableName() . ' `c`', '`c`.`id` = `cc`.`contact_id`')
                ->join('LEFT JOIN', User::tableName() . ' `u`', '`u`.`id` = `cc`.`manager_id`')
                ->join('LEFT JOIN', ObjectApartment::tableName() . ' `oa`', '`oa`.`id` = `cc`.`object_id`')
                ->join('LEFT JOIN', ContactComment::tableName() . ' `com`', '`com`.`id` = (SELECT MAX(`id`) FROM `contact_comment` WHERE `contact_comment`.`contact_id` = `cc`.`contact_id`)')
                ->join('LEFT JOIN', ContractSolution::tableName() . ' `cs`', '`cs`.`id` = `cc`.`solution_id`')
                ->where(['`cs`.`name`' => null]);
        $query2 = new Query();
        $query2->select($columns)
                ->from(ContactContract::tableName() . ' as `cc`')
                ->join('LEFT JOIN', Contact::tableName() . ' `c`', '`c`.`id` = `cc`.`contact_id`')
                ->join('LEFT JOIN', User::tableName() . ' `u`', '`u`.`id` = `cc`.`manager_id`')
                ->join('LEFT JOIN', ObjectApartment::tableName() . ' `oa`', '`oa`.`id` = `cc`.`object_id`')
                ->join('LEFT JOIN', ContactComment::tableName() . ' `com`', '`com`.`id` = (SELECT MAX(`id`) FROM `contact_comment` WHERE `contact_comment`.`contact_id` = `cc`.`contact_id`)')
                ->join('LEFT JOIN', ContractSolution::tableName() . ' `cs`', '`cs`.`id` = `cc`.`solution_id`')
                ->where(['is not', '`cs`.`name`', null]);

        $query->union($query2, true);
        $query->limit($request_data['length'])
                ->offset($request_data['start']);

        //$dump = $query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;

        $contracts = $query->all();
        $contract_widget = new ContractTableWidget();
        $contract_widget->contracts = $contracts;
        $data = $contract_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

    public function actionSolution() {
        $solution_form = new SolutionForm();
        $post = Yii::$app->request->post();
        $solution_form->load($post);
        if ($solution_form->validate()) {
            $contract = ContactContract::find()->where(['id' => $post['id']])->one();
            if ($contract) {
                if ($contract->solution($post['type'], $post['comment'])) {
                    $solution_name = $contract->getSolutionName();
                    if ($solution_name == 'approved') {
                        $object = ObjectApartment::find()->where(['id' => $contract->object_id])->one();
                        if ($object) {
                            $object->is_sold = 1;
                            $object->save();
                        }
                    }
                    $this->json(['solution' => $solution_name], 200);
                }
                $this->json([], 500);
            }
        }
        $this->json([], 415);
    }

    public function actionGetByContactId() {
        $contact_id = Yii::$app->request->get('id');
        $type = Yii::$app->request->get('type');
        $approve_solution = null;
        switch ($type) {
            case "approved":
                $approve_solution = ContractSolution::find()->where(['name' => 'approved'])->one();
                break;
            case "revision":
                $approve_solution = ContractSolution::find()->where(['name' => 'revision'])->one();
                break;
        }
        $where = ['contact_id' => $contact_id];
        if ($approve_solution) {
            $where['solution_id'] = $approve_solution->id;
        }
        $contracts = ContactContract::find()->where($where)->with('object')->all();
        $contracts_array = [];
        foreach ($contracts as $contract) {
            $contracts_array[$contract->id] = $contract->object->number;
        }
        $this->json($contracts_array, 200);
    }

    public function actionGetPaymentsByContactId() {
        $contract_id = Yii::$app->request->get('contract_id');
        $contract = ContactContract::find()->where(['id' => $contract_id])->with(['object', 'payments'])->one();
        $contracts_data['id'] = $contract->id;
        $contracts_data['object'] = $contract->object->number;
        $contracts_data['link'] = $contract->object->link;
        $contracts_data['total_cost'] = $contract->price;
        $contracts_data['comment'] = $contract->comment;
        $contracts_data['agreement'] = $contract->agreement;
        $total_payment = 0;
        foreach ($contract->payments as $i => $payment) {
            $total_payment+= $payment->amount;
            $contracts_data['payments'][$i]['id'] = $payment->id;
            $contracts_data['payments'][$i]['date'] = date('d-m-Y', strtotime($payment->system_date));
            $contracts_data['payments'][$i]['amount'] = $payment->amount;
        }
        $contracts_data['total_payment'] = $total_payment;
        $this->json($contracts_data, 200);
    }

    public function actionGetDetailsByContactId() {
        $contract_id = Yii::$app->request->get('contract_id');
        try {
            $contract_details = ContactContract::getDetailById($contract_id);
            $this->json($contract_details, 200);
        } catch (\Exception $ex) {
            $this->json(false, 500);
        }
    }

    public function actionPayment() {
        $payment_form = new PaymentForm();
        $post = Yii::$app->request->post();
        $payment_form->load($post);
        if ($payment_form->validate()) {
            $contract_payment = new ContractPayment();
            $contract_payment->manager_id = Yii::$app->user->identity->id;
            if ($contract_payment->add($payment_form->id, $payment_form->contract, $payment_form->amount, $payment_form->comment)) {
                $history_text = $contract_payment->getHistoryText();
                $response_date = [
                    'id' => $contract_payment->id,
                    'system_date' => date('d-m-Y G:i:s', strtotime($contract_payment->system_date)),
                    'history' => $history_text
                ];
                $this->json($response_date, 200);
            }
        }
    }

    public function actionGetPayments() {
        $contact_id = Yii::$app->request->get('contract_id');
        $payments = ContractPayment::find()->where(['contract_id' => $contact_id])->all();
        $payments_array = [];
        foreach ($payments as $i => $payment) {
            $payments_array[$i]['id'] = $payment->id;
            $payments_array[$i]['date'] = date('d-m-Y', strtotime($payment->system_date));
            $payments_array[$i]['amount'] = $payment->amount;
        }
        $this->json($payments_array, 200);
    }

    public function actionComment() {
        $contact_id = Yii::$app->request->post('contract_id');
        $comment = Yii::$app->request->post('comment');
        if (ContactContract::updateAll(['comment' => $comment], ['id' => $contact_id])) {
            $this->json([], 200);
        }
        $this->json(500);
    }

    public function actionDownload() {
        $contact_id = Yii::$app->request->get('contract_id');
        $contract = ContactContract::find()->select('agreement')->where(['id' => $contact_id])->one();
        $agreement = $contract->agreement;
        Yii::$app->response->sendFile(Yii::getAlias('@app') . '/agreements/' . $agreement, null, ['inline' => false])->send();
    }

}
