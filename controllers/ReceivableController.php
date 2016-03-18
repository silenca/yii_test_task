<?php

namespace app\controllers;

use yii\filters\AccessControl;
use app\models\User;
use app\models\ContactContract;
use app\models\Contact;
use app\models\ObjectApartment;
use app\models\ContractPayment;
use app\components\widgets\ReceivableWidget;
use yii\db\Query;
use Yii;

class ReceivableController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata'],
                        'allow' => true,
                        'roles' => ['receivables'],
                    ]
                ],
            ],
        ];
    }

    public function actionIndex() {
        $managers = User::find()
                ->where(['role' => User::ROLE_MANAGER])
                ->all();
        return $this->render('index', ['managers' => $managers]);
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $query = new Query();
        $subquery = new Query();
        $subquery->select('`cc`.`id`, `cc`.`contact_id`, `c`.`first_name`, `oa`.`link`, `cc`.`price`,`u`.`firstname`,`cc`.`system_date`')
                ->from(ContactContract::tableName() . ' as `cc`')
                ->join('LEFT JOIN', Contact::tableName() . ' `c`', '`c`.`id` = `cc`.`contact_id`')
                ->join('LEFT JOIN', ObjectApartment::tableName() . ' `oa`', '`oa`.`id` = `cc`.`object_id`')
                ->join('LEFT JOIN', User::tableName() . ' `u`', '`u`.`id` = `cc`.`manager_id`');
        $subquery->limit($request_data['length'])
                ->offset($request_data['start']);
        if (!empty($request_data['columns'][0]['search']['value'])) {
            $subquery->andWhere(['like', '`c`.`first_name`', $request_data['columns'][0]['search']['value']]);
        }
        if (!empty($request_data['columns'][1]['search']['value'])) {
            $subquery->andWhere(['`u`.`id`' => $request_data['columns'][1]['search']['value']]);
        }
        $subquery_sql = $subquery->createCommand()->rawSql;
        $query->select('`ctr`.`id`, `ctr`.`contact_id`, `ctr`.`first_name`, `ctr`.`link`, `ctr`.`price`,`ctr`.`firstname`,`cp`.`amount`, `cp`.`system_date`, `cp`.`id` as payment_id')
                ->from("(" . $subquery_sql . ") as `ctr`")
                ->join('LEFT JOIN', ContractPayment::tableName() . ' `cp`', '`cp`.`contract_id` = `ctr`.`id`');
        
        if (!empty($request_data['columns'][2]['search']['value']) and ( !empty($request_data['columns'][3]['search']['value']))) {
            $query->where(['between', '`cp`.`system_date`', $request_data['columns'][2]['search']['value'], $request_data['columns'][3]['search']['value']]);
            $query->orWhere(['between', '`ctr`.`system_date`', $request_data['columns'][2]['search']['value'], $request_data['columns'][3]['search']['value']]);
        }
        $dump = $query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;

        $receivables = $query->all();

        $receivable_widget = new ReceivableWidget();
        $receivable_widget->receivables = $receivables;
        $data = $receivable_widget->run();
        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval(0),
            "recordsFiltered" => intval(0),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

}
