<?php
/**
 * Created by PhpStorm.
 * User: phobos
 * Date: 11/4/15
 * Time: 11:06 AM
 */

namespace app\controllers;

use app\models\ContactStatusHistory;
use yii\filters\AccessControl;
use Yii;
use yii\db\Query;
use app\models\User;
use app\models\Call;
use app\models\CallManager;
use app\models\ContactShow;
use app\models\ContactVisit;
use app\models\Contact;
use app\components\widgets\ReportsWidget;


class ReportsController extends BaseController
{
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata'],
                        'allow' => true,
                        'roles' => ['reports'],
                    ]
                ],
            ],
        ];
    }
    
    public function actionIndex() {
        $managers = User::find()->all();
        return $this->render('index',['managers' => $managers]);
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();


        $user_id = null;
        $date_start = date('Y-m-01 00:00:00');
        $date_end = date('Y-m-t 23:59:59');
        $tags_id = null;

        if ($request_data['columns'][0]['search']['value']) {
            $user_id = $request_data['columns'][0]['search']['value'];
        }
        if ($request_data['columns'][1]['search']['value']) {
            $date_start = $request_data['columns'][1]['search']['value'];
        }
        if ($request_data['columns'][2]['search']['value']) {
            $date_end = $request_data['columns'][2]['search']['value'];
        }
        if ($request_data['columns'][3]['search']['value']) {
            $tags_id = explode(',', $request_data['columns'][3]['search']['value']);
        }
        $show_archive_tags = Yii::$app->user->identity->getSetting('use_archive_tags');


        //$result_data = ['id', 'incoming', 'outgoing', 'tags', 'served'];

        /*
        Получаем исходящие звонки для каждого опрератора сгруппированные по статусам
        select `user`.`id`,`user`.`firstname`, count(*) as `count`, `call`.`status` from `call`
        left join `call_manager` on `call_manager`.`call_id` = `call`.`id`
        left join `user` on `user`.`id` = `call_manager`.`manager_id`
        left join `tag` on `call`.`tag_id` = `tag`.`id`
        where `call`.`type` = 'incoming' and `user`.`id` is not null and `call`.`status` <> 'new'
        group by `user`.`id`, `call`.`status`
         */
        $incomingQuery = new Query();
        $incomingQuery->select('`user`.`id`, `user`.`firstname`, count(*) as `count`, `call`.`status`')
            ->from('`call`')
            ->join("LEFT JOIN", '`call_manager`','`call_manager`.`call_id` = `call`.`id`')
            ->join("LEFT JOIN", '`user`','`user`.`id` = `call_manager`.`manager_id`')
            ->join("LEFT JOIN", '`tag`','`call`.`tag_id` = `tag`.`id`')
            ->where(['`call`.`type`' => 'incoming'])
            ->andWhere(['is not', '`user`.`id`', null])
            ->andWhere(['<>', '`call`.`status`', 'new'])
            ->groupBy(['`user`.`id`', '`call`.`status`']);
        if ($user_id) {
            $incomingQuery->andWhere(['`user`.`id`' => $user_id]);
        }
        if ($date_start) {
            $incomingQuery->andWhere(['>=','`call`.`date_time`', $date_start]);
        }
        if ($date_end) {
            $incomingQuery->andWhere(['<=','`call`.`date_time`', $date_end]);
        }
        if ($tags_id) {
            $incomingQuery->andWhere(['in', '`call`.`tag_id`', $tags_id]);
        }
        if (!$show_archive_tags) {
            $incomingQuery->andWhere('(`tag`.`is_deleted` = 0 or `call`.`tag_id` is null)');
        }
        $incomings = $incomingQuery->all();
        $incomingData = [];
        foreach ($incomings as $incoming) {
            $incomingData[$incoming['id']]['success'] = 0;
            if ($incoming['status'] == 'answered')
                $incomingData[$incoming['id']]['success'] += $incoming['count'];
            $incomingData[$incoming['id']]['all'] += $incoming['count'];
        }
        /*
        Получаем входящие звонки для каждого опрератора сгруппированные по статусам
        select `user`.`id`, `user`.`firstname`, count(*) as `count`, `call`.`status` from `call`
        left join `call_manager` on `call_manager`.`call_id` = `call`.`id`
        left join `user` on `user`.`id` = `call_manager`.`manager_id`
        left join `tag` on `call`.`tag_id` = `tag`.`id`
        where `call`.`type` = 'outgoing' and `user`.`id` is not null and `call`.`status` <> 'new'
        group by `user`.`id`, `call`.`status`
         */

        $outgoingQuery = new Query();
        $outgoingQuery->select('`user`.`id`, `user`.`firstname`, count(*) as `count`, `call`.`status`')
            ->from('`call`')
            ->join("LEFT JOIN", '`call_manager`','`call_manager`.`call_id` = `call`.`id`')
            ->join("LEFT JOIN", '`user`','`user`.`id` = `call_manager`.`manager_id`')
            ->join("LEFT JOIN", '`tag`','`call`.`tag_id` = `tag`.`id`')
            ->where(['`call`.`type`' => 'outgoing'])
            ->andWhere(['is not', '`user`.`id`', null])
            ->andWhere(['<>', '`call`.`status`', 'new'])
            ->groupBy(['`user`.`id`', '`call`.`status`']);
        if ($user_id) {
            $outgoingQuery->andWhere(['`user`.`id`' => $user_id]);
        }
        if ($date_start) {
            $outgoingQuery->andWhere(['>=','`call`.`date_time`', $date_start]);
        }
        if ($date_end) {
            $outgoingQuery->andWhere(['<=','`call`.`date_time`', $date_end]);
        }
        if ($tags_id) {
            $outgoingQuery->andWhere(['in', '`call`.`tag_id`', $tags_id]);
        }
        if (!$show_archive_tags) {
            $outgoingQuery->andWhere('(`tag`.`is_deleted` = 0 or `call`.`tag_id` is null)');
        }
        $outgoings = $outgoingQuery->all();
        $outgoingData = [];
        foreach ($outgoings as $outgoing) {
            $outgoingData[$outgoing['id']]['success'] = 0;
            if ($outgoing['status'] == 'answered')
                $outgoingData[$outgoing['id']]['success'] += $outgoing['count'];
            $outgoingData[$outgoing['id']]['all'] += $outgoing['count'];
        }

        /*
         Получение кол-во обслуженных клиентов операторами
         select `user`.`id`, `user`.`firstname`, count(distinct `call`.`contact_id`) as `count` from `call`
        left join `call_manager` on `call_manager`.`call_id` = `call`.`id`
        left join `user` on `user`.`id` = `call_manager`.`manager_id`
        left join `tag` on `call`.`tag_id` = `tag`.`id`
        where `user`.`id` is not null
        group by `user`.`id`
         */

        $servedQuery = new Query();
        $servedQuery->select('`user`.`id`, `user`.`firstname`, count(distinct `call`.`contact_id`) as `count`')
            ->from('`call`')
            ->join("LEFT JOIN", '`call_manager`','`call_manager`.`call_id` = `call`.`id`')
            ->join("LEFT JOIN", '`user`','`user`.`id` = `call_manager`.`manager_id`')
            ->join("LEFT JOIN", '`tag`','`call`.`tag_id` = `tag`.`id`')
            ->where(['is not', '`user`.`id`', null])
            ->andWhere(['<>', '`call`.`status`', 'new'])
            ->groupBy(['`user`.`id`']);
        if ($user_id) {
            $servedQuery->andWhere(['`user`.`id`' => $user_id]);
        }
        if ($date_start) {
            $servedQuery->andWhere(['>=','`call`.`date_time`', $date_start]);
        }
        if ($date_end) {
            $servedQuery->andWhere(['<=','`call`.`date_time`', $date_end]);
        }
        if ($tags_id) {
            $servedQuery->andWhere(['in', '`call`.`tag_id`', $tags_id]);
        }
        if (!$show_archive_tags) {
            $servedQuery->andWhere('(`tag`.`is_deleted` = 0 or `call`.`tag_id` is null)');
        }
        $serveds = $servedQuery->all();
        $servedsData = [];
        foreach ($serveds as $served) {
            $servedsData[$served['id']] = $served['count'];
        }
        /*
         Получаем все теги для пользователя, по которым он делал звонки
         select  distinct `tag`.`id` as `tag_id`, `tag`.`name`, `tag`.`is_deleted`, `user`.`id` as `user_id`, `user`.`firstname` from `call`
        left join `call_manager` on `call_manager`.`call_id` = `call`.`id`
        left join `user` on `user`.`id` = `call_manager`.`manager_id`
        left join `tag` on `tag`.`id` = `call`.`tag_id`
        where `tag`.`name` is not null and `user`.`id` is not null
        group by `user`.`id`, `call`.`tag_id`
         */
        $userCallTagsQuery = new Query();
        $userCallTagsQuery->select('distinct `tag`.`id` as `tag_id`, `tag`.`name`, `tag`.`is_deleted`, `user`.`id` as `user_id`, `user`.`firstname`')
            ->from('`call`')
            ->join("LEFT JOIN", '`call_manager`','`call_manager`.`call_id` = `call`.`id`')
            ->join("LEFT JOIN", '`user`','`user`.`id` = `call_manager`.`manager_id`')
            ->join("LEFT JOIN", '`tag`','`tag`.`id` = `call`.`tag_id`')
            ->where(['is not', '`tag`.`name`', null])
            ->andWhere(['<>', '`call`.`status`', 'new'])
            ->andWhere(['is not', '`user`.`id`', null])
            ->groupBy(['`user`.`id`', '`call`.`tag_id`']);
        if ($user_id) {
            $userCallTagsQuery->andWhere(['`user`.`id`' => $user_id]);
        }
        if ($date_start) {
            $userCallTagsQuery->andWhere(['>=','`call`.`date_time`', $date_start]);
        }
        if ($date_end) {
            $userCallTagsQuery->andWhere(['<=','`call`.`date_time`', $date_end]);
        }
        if (!$show_archive_tags) {
            $userCallTagsQuery->andWhere(['`tag`.`is_deleted`' => 0]);
        }

        $userCallTags = $userCallTagsQuery->all();
        $userCallTagsData = [];
        foreach ($userCallTags as $i => $userCallTag) {
            $userCallTagsData[$userCallTag['user_id']][] = [
                'tag_id' => $userCallTag['tag_id'],
                'name' => $userCallTag['name'],
                'is_deleted' => $userCallTag['is_deleted']
            ];
        }

        $usersModel = User::find();
        if ($user_id) {
            $usersModel->where(['`user`.`id`' => $user_id]);
        }
        $users = $usersModel->asArray()->all();
        $report_widget = new ReportsWidget();
        $report_widget->users = $users;
        $report_widget->incomings = $incomingData;
        $report_widget->outgoings = $outgoingData;
        $report_widget->serveds = $servedsData;
        $report_widget->userCallTags = $userCallTagsData;
        $data = $report_widget->run();
        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval(0),
            "recordsFiltered" => intval(0),
            "data" => $data   // total data array
        );
        echo json_encode($json_data, true);
        die;
    }
}