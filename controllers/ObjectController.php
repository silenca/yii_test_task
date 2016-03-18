<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\ObjectQueue;
use app\models\ObjectHouse;
use app\models\ObjectFloor;
use app\models\ObjectApartment;
use app\components\widgets\ObjectTableWidget;

class ObjectController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata', 'getqueue', 'gethousing', 'getfloor', 'getapartment', 'get-all-fields'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['edit-comment'],
                        'allow' => true,
                        'roles' => ['supervisor'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'edit-comment' => ['post']
                ],
            ],
        ];
    }

    public function actionIndex() {
        $queues = ObjectQueue::find()->all();
        $floors = ObjectFloor::find()->distinct()->orderBy(['floor' => SORT_ASC])->all();
        $houses = ObjectHouse::find()->orderBy(['id' => SORT_ASC])->all();
        $layouts = ObjectApartment::find()->distinct()->select('layout')->all();
        return $this->render('index', [
                    'queues' => $queues,
                    'floors' => $floors,
                    'houses' => $houses,
                    'layouts' => $layouts,
        ]);
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $columns = $this->getTableColumns();
        $query = new Query();
        $query->select([
                    '`oq`.`queue`',
                    '`oh`.`housing`',
                    '`of`.`floor`',
                    '`oa`.*'
                ])
                ->from(ObjectQueue::tableName() . ' oq')
                ->join('JOIN', ObjectHouse::tableName() . ' `oh`', '`oh`.`queue_id` = `oq`.`id`')
                ->join('JOIN', ObjectFloor::tableName() . ' `of`', '`of`.`house_id` = `oh`.`id`')
                ->join('JOIN', ObjectApartment::tableName() . ' `oa`', '`oa`.`floor_id` = `of`.`id`');
        $total_count = $query->count();

        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$request_data['order'][0]['column']];
            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                '`oa`.`id`' => SORT_DESC
            ];
        }
        if (!empty($request_data['columns'][0]['search']['value'])) {
            $query->where(['`oq`.`queue`' => $request_data['columns'][0]['search']['value']]);
        }
        if (!empty($request_data['columns'][1]['search']['value'])) {
            $query->andWhere(['`oh`.`id`' => $request_data['columns'][1]['search']['value']]);
        }
        if (!empty($request_data['columns'][2]['search']['value'])) {
            $query->andWhere(['`of`.`id`' => $request_data['columns'][2]['search']['value']]);
        }
        if (!empty($request_data['columns'][3]['search']['value'])) {
            $query->andWhere(['like', '`oa`.`number`', $request_data['columns'][3]['search']['value']]);
        }
        if (!empty($request_data['columns'][4]['search']['value'])) {
            if (is_numeric($request_data['columns'][4]['search']['value']) && $request_data['columns'][4]['search']['value'] > 0) {
                $min_area = $request_data['columns'][4]['search']['value'] - 5;
                $max_area = $request_data['columns'][4]['search']['value'] + 5;
                $query->andWhere("`oa`.`area` between $min_area and $max_area");
            }
        }
        if (!empty($request_data['columns'][5]['search']['value'])) {
            if ($request_data['columns'][5]['search']['value'] == 'active') {
                $query->andWhere(['`oa`.`is_sold`' => '1']);
            } else {
                $query->andWhere(['`oa`.`is_sold`' => '0']);
            }
        }
        if (!empty($request_data['columns'][6]['search']['value'])) {
            $query->andWhere(['`oa`.`layout`' => $request_data['columns'][6]['search']['value']]);
            //$query->andWhere(['like','`oa`.`layout`',$request_data['columns'][6]['search']['value']]);
        }


        $query->orderBy($sorting);
        $query->limit($request_data['length'])
                ->offset($request_data['start']);

        //$dump = $query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        //$command = $query->createCommand();
        //$objects = $command->queryAll();
        $total_filtering_count = $query->count();
        $objects = $query->all();

        $object_widget = new ObjectTableWidget();
        $object_widget->objects = $objects;
        $data = $object_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

//    public function actionDelete() {
//        $object_id = Yii::$app->request->post('id');
//        if (ObjectApartment::deleteById($object_id)) {
//            $this->json(false, 200);
//        }
//        $this->json(false, 500);
//    }

    public function actionGetqueue() {
        $queues = ObjectQueue::find()->select('id, queue')->asArray()->all();
        $this->json($queues, 200);
    }

    public function actionGethousing() {
        $queue = Yii::$app->request->get('queue');
        $houses = ObjectHouse::getHouseByQueue($queue);
        $this->json($houses, 200);
    }

    public function actionGetfloor() {
        $house = Yii::$app->request->get('house');
        $floors = ObjectFloor::getFloorByHouse($house);
        $this->json($floors, 200);
    }

    public function actionGetapartment() {
        $floor = Yii::$app->request->get('floor');
        $apartments = ObjectApartment::getApartmentByFloor($floor, $only_on_show = true);
        $this->json($apartments, 200);
    }

    public function actionGetAllFields() {
        $queue = Yii::$app->request->get('queue');
        $house = Yii::$app->request->get('house');
        $floor = Yii::$app->request->get('floor');
        $houses = ObjectHouse::getHouseByQueue($queue);
        $floors = ObjectFloor::getFloorByHouse($house);
        $apartments = ObjectApartment::getApartmentByFloor($floor, $only_on_show = true);
        $data = [
            'houses' => $houses,
            'floors' => $floors,
            'apartments' => $apartments,
        ];
        $this->json($data, 200);
    }

    private function getTableColumns() {
        return [
            1 => '`oq`.`queue`',
            2 => '`oh`.`housing`',
            3 => '`of`.`floor`',
            4 => '`oa`.`number`',
            5 => '`oa`.`area`',
            6 => '`oa`.`is_sold`',
            7 => '`oa`.`room_numbers`',
        ];
    }

    public function actionEditComment() {
        $object_id = Yii::$app->request->post('object-id');
        $object_comment = Yii::$app->request->post('object-comment');

        try {
            $object = ObjectApartment::find()->where(['id' => $object_id])->one();

            if (!$object) {
                $this->json(false, 404, 'Обьект не найден');
            }
            $object->comment = $object_comment;

            if ($object->save()) {
                $this->json(false, 200);
            } else {
                $errors = $object->getErrors();
                $this->json(false, 500, $errors);
            }
        } catch (\Exception $ex) {
            $this->json(false, 500);
        }


    }

}
