<?php

namespace app\controllers;


use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\ManagerNotification;
use app\components\widgets\ManagerNotifyTableWidget;
use app\models\ContactComment;
use app\models\Contact;
use app\models\Jivosite;
use app\models\JivositeManagerNotification;
use app\models\Action;
use app\models\ManagerNotificationAction;
use Yii;

class ManagernotifyController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata', 'view'],
                        'allow' => true,
                        'roles' => ['manager'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $total_count = ManagerNotification::find()->where(['manager_id' => Yii::$app->user->identity->id])->count();
        $columns = ManagerNotification::getTableColumns();
        $query = new Query();
        $query->select($columns)
                ->from(ManagerNotification::tableName() . ' as `mn`')
                ->join('LEFT JOIN', Contact::tableName() . ' `c`', '`c`.`id` = `mn`.`contact_id`')
                ->join('LEFT JOIN', ContactComment::tableName() . ' `cc`', '`cc`.`id` = (SELECT MAX(`id`) FROM `contact_comment` WHERE `contact_comment`.`contact_id` = `mn`.`contact_id`)')
                ->join('LEFT JOIN', JivositeManagerNotification::tableName() . ' `jmn`', '`jmn`.`manager_notification_id` = `mn`.`id`')
                ->join('LEFT JOIN', Jivosite::tableName() . ' `js`', '`js`.`id` = `jmn`.`jivosite_id`')
                ->join('LEFT JOIN', ManagerNotificationAction::tableName() . ' `mna`', '`mna`.`manager_notification_id` = `mn`.`id`')
                ->join('LEFT JOIN', Action::tableName() . ' `ac`', '`ac`.`id` = `mna`.`action_id`')
                ->where(['`mn`.`manager_id`' => Yii::$app->user->identity->id]);

        $query->orderBy(['`mn`.`id`' => SORT_DESC]);
        $query->limit($request_data['length'])
                ->offset($request_data['start']);

        $notifications = $query->all();
        $manager_notify_widget = new ManagerNotifyTableWidget();
        $manager_notify_widget->notifications = $notifications;
        $data = $manager_notify_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

    public function actionView() {
        $id = Yii::$app->request->post('id');
        ManagerNotification::updateAll(['viewed' => '1'], ['id' => $id]);
        $this->json([], 200);
    }

    private function getTableColumns() {
        return [
            '`la`.`system_date`',
            '`la`.`type`',
            '`c`.`first_name`',
            '`la`.`object_link`',
            '`la`.`schedule_date`',
            '`u`.`firstname` AS "manager_name"',
            '`cc`.`comment`',
            '`la`.`contact_id`',
        ];
    }

}
