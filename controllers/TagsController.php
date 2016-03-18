<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Tag;
use app\components\widgets\TagTableWidget;

class TagsController extends BaseController {
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'getdata',
                            'edit',
                            'delete',
                        ],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'edit' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $total_count = Tag::find()->count();
        $columns = Tag::getTableColumns();
        $sorting = [];
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$request_data['order'][0]['column']];

            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                'id' => SORT_DESC
            ];
        }
        $query = Tag::find();

        if (!empty($request_data['columns'][0]['search']['value'])) {
            $query->where(['like', 'name', $request_data['columns'][0]['search']['value']]);
        }
        if (!empty($request_data['columns'][1]['search']['value'])) {
            $query->where(['like', 'description', $request_data['columns'][1]['search']['value']]);
        }

        $total_filtering_count = $query->count();
        $query
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

        $tags = $query->all();
        $tags_widget = new TagTableWidget();
        $tags_widget->tags = $tags;
        $data = $tags_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

    public function actionEdit() {
        $post = Yii::$app->request->post();
        try {
            if (isset($post['id']) && !empty($post['id'])) {
                $tag = Tag::getById($post['id']);
//            if (!Yii::$app->user->can('updateContact', ['contact' => $contact])) {
//                $this->json(false, 403, 'Ќедостаточно прав дл€ редактировани€');
//            }
            } else {
                $tag = new Tag();
            }
            unset($post['_csrf']);
            unset($post['id']);
            $tag->attributes = $post;
            if ($tag->save()) {
                $this->json(['id' => $tag->id], 200);
            } else {
                $this->json(false, 415, $tag->getErrors());
            }
        } catch (\Exception $ex) {
                $this->json(false, 500);
        }
    }

    public function actionDelete() {
        $tag_id = Yii::$app->request->post('id');
        $tag = Tag::getById($tag_id);
        if ($tag->delete()) {
            $this->json(false, 200);
        }
    }
}