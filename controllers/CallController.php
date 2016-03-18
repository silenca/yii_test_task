<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\User;
use app\models\Contact;
use app\models\Call;
use app\models\CallManager;
use app\models\MissedCall;
use app\components\widgets\CallTableWidget;

class CallController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                //'delete' => ['post']
                ],
            ],
        ];
    }

    public function actionIndex() {
        if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            return $this->render('index');
        } else {
            $managers = User::find()->where(['role' => User::ROLE_MANAGER])->all();
            return $this->render('index', [
                        'managers' => $managers
            ]);
        }
    }

    public function actionView() {
        $call_id = Yii::$app->request->post('id');
        $manager_id = Yii::$app->user->identity->id;
        if (MissedCall::remove($call_id, $manager_id)) {
            $this->json([], 200);
        }
        $this->json([], 404);
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $columns = Call::getTableColumns();
        $columns_index = array_keys($columns);
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$columns_index[$request_data['order'][0]['column']]];
            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                '`c`.`id`' => SORT_DESC
            ];
        }
        $manager_id = null;
        if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            $manager_id = Yii::$app->user->identity->id;
            //$query->andWhere(['`u`.`id`' => Yii::$app->user->identity->id]);
        } else {
            if (!empty($request_data['columns'][1]['search']['value'])) {
                $manager_id = $request_data['columns'][1]['search']['value'];
                //$query->andWhere(['`u`.`id`' => $request_data['columns'][1]['search']['value']]);
            }
        }
        $query = new Query();
        $from_query = new Query();
        $from_query->from(Call::tableName() . " as `c`")
                ->limit($request_data['length'])
                ->offset($request_data['start']);
        $from_query->orderBy($sorting);

        $new_calls = new Query();
        $new_calls->select('id')
                ->from(Call::tableName() . " as `c`")
                ->where(['<>', '`c`.`status`', 'new']);
        $new_calls_sql = $new_calls->createCommand()->rawSql;
        $missed_calls = new Query();
        $missed_calls->select('`mc`.`call_id`')
                ->from(MissedCall::tableName() . " `mc`")
                ->where(['`mc`.`manager_id`' => Yii::$app->user->identity->id])
                ->andWhere("`mc`.`call_id` IN ($new_calls_sql)");
        //$dump = $missed_calls->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        $call_ids = $missed_calls->all();

        //$from_query->from(Call::tableName());

        $query->select(Call::buildSelectQuery());
        if ($manager_id) {
            $query->from(Call::tableName() . " `c`");
            $query->andWhere(['`u`.`id`' => $manager_id]);
            $query->limit($request_data['length'])
                    ->offset($request_data['start']);
            $query->orderBy($sorting);
        } else {
            $from_query_sql = $from_query->createCommand()->rawSql;
            $query->from("(" . $from_query_sql . ") `c`");
        }
        $query->join('LEFT JOIN', Contact::tableName() . ' `ct`', '`ct`.`id` = `c`.`contact_id`')
                ->join('LEFT OUTER JOIN', CallManager::tableName() . ' `cm`', '`cm`.`call_id` = `c`.`id`')
                ->join('LEFT OUTER JOIN', User::tableName() . ' `u`', '`cm`.`manager_id` = `u`.`id`');

        $query->andWhere(['<>', '`c`.`status`', 'new']);
        $total_count = $query->count();

        if (!empty($request_data['columns'][0]['search']['value'])) {
            $value = $request_data['columns'][0]['search']['value'];
            switch ($value) {
                case "incoming":
                    $query->andWhere(['`c`.`type`' => 'incoming', '`c`.`status`' => 'answered']);
                    break;
                case "outgoing":
                    $query->andWhere(['`c`.`type`' => 'outgoing']);
                    break;
                case "missed":
                    $query->andWhere(['`c`.`type`' => 'outgoing', '`c`.`status`' => 'missed']);
                    break;
                case "failure":
                    $query->andWhere(['`c`.`status`' => 'failure']);
                    break;
            }
        }

        $total_filtering_count = $query->count();

        $query->orderBy($sorting);
        $dump = $query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        $calls = $query->all();

        $call_widget = new CallTableWidget();
        $call_widget->calls = $calls;
        $call_widget->calls_missed = $call_ids;
        $data = $call_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

}
