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

class CallController extends BaseController
{
    protected $columns = [
        ['name' => 'id', 'label' => 'ID', 'visible' => 'false'],
        ['name' => 'date', 'label' => 'Дата'],
        ['name' => 'time', 'label' => 'Время'],
        ['name' => 'type', 'type' => 'select', 'label' => 'Тип'],
        ['name' => 'manager', 'type' => 'select', 'label' => 'Менеджер', 'default' => 0],
        ['name' => 'contact', 'label' => 'Клиент'],
        ['name' => 'record', 'label' => 'Прослушать'],
    ];

    public function behaviors()
    {
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

    public function actionIndex()
    {
        return $this->render('index', [
            'columns' => $this->getColumnDefinitions(),
        ]);
    }

    public function actionView()
    {
        $call_id = Yii::$app->request->post('id');
        $manager_id = Yii::$app->user->identity->id;
        if (MissedCall::remove($call_id, $manager_id)) {
            $this->json([], 200);
        }
        $this->json([], 404);
    }

    public function actionGetdata()
    {
        $request_data = Yii::$app->request->get();

        $query = Call::find()
            ->alias('c')
            ->joinWith(['callManagers.manager'])
            ->with('callManagers', 'callManagers.manager', 'contact.tags', 'missedCall')
            ->andWhere(['<>', '`c`.`status`', 'new'])
            ->distinct();

        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();
        $columns = Call::getTableColumns();
        $columns_index = array_keys($columns);

        //Sorting
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

        //User role restrictions
        switch ($user_role) {
            case 'operator':
                $query->andWhere([User::tableName() . '.id' => $user_id]);
                break;
            case 'manager':
                $query->andWhere([User::tableName() . '.role' => [User::ROLE_MANAGER, User::ROLE_OPERATOR]]);
                break;
        }
        $total_count = $query->count();

        //Filtering
        $filter_status = $request_data['columns'][4]['search']['value'];
        $filter_manager_id = $request_data['columns'][5]['search']['value'];
        $filter_tag_name = $request_data['columns'][6]['search']['value'];
        if (!empty($filter_status)) {
            $filter_status = explode('|', $filter_status);
            $statuses = explode('_', $filter_status[0]);
            $types = explode('_', $filter_status[1]);
            if (count($statuses) > 0) {
                $query->andWhere(['c.status' => $statuses]);
            }
            if (count($types) > 0) {
                $query->andWhere(['c.type' => $types]);
            }
        }
        if (!empty($filter_tag_name)) {
            $query->joinWith('contact.tags')->andWhere(['like', 'tag.name', $filter_tag_name]);
        }
        if (!empty($filter_manager_id)) {
            $query->andWhere([User::tableName() . '.id' => $filter_manager_id]);
        }
//        $dump = $query->createCommand()->rawSql;
        $total_filtering_count = $query->count();

        $dump = $query->createCommand()->rawSql;
        $query->limit($request_data['length'])
            ->offset($request_data['start'])
            ->orderBy($sorting);

        $calls = $query->all();

        $call_widget = new CallTableWidget();
        $call_widget->calls = $calls;
        $call_widget->user_role = $user_role;
        $call_widget->user_id = $user_id;
        $call_widget->columns = $this->columns;
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

    protected function getColumnDefinitions()
    {
        $columns = $this->columns;

        foreach($columns as $k=>$column) {
            if($column['type'] == 'select') {
                $method = 'collect'.ucfirst($column['name']).'Options';
                if(method_exists($this, $method)) {
                    $columns[$k]['options'] = $this->$method();
                }
            }
        }

        return $columns;
    }

    protected function collectManagerOptions()
    {
        $options = [];
        $managers = [];
        switch(\Yii::$app->user->identity->role) {
            case User::ROLE_ADMIN:
                $managers = User::find()->all();
                break;
            case User::ROLE_MANAGER:
                $managers = User::find()->where(['role' => [User::ROLE_MANAGER, User::ROLE_OPERATOR]])->all();
                break;
        }

        foreach($managers as $manager) {
            $options[$manager->id] = $manager->firstname;
        }

        return $options;
    }

    protected function collectTypeOptions()
    {
        $options = [];
        $statuses = Call::getCallStatuses();

        foreach($statuses as $type) {
            $options[$type['name']] = $type['label'];
        }

        return $options;
    }
}
