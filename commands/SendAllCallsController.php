<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Call;
use yii\console\Controller;

class SendAllCallsController extends Controller {

    private $sendedCount = 0;
    private $percent = 0;
    private $count = 0;
    private $limit = 500;

    function actionIndex() {
        set_time_limit(0);
        $query = Call::find()->where('`call`.`id` NOT IN (SELECT `call_id` FROM `fail_export_call`) AND `user`.`int_id` is not null AND  `call`.`status` <> "new"  AND `call`.`sended_crm` = 0')
            ->joinWith(['callManagers.manager'])
            ->groupBy('`call`.`id`');
        $this->count = $query->count();
        echo 'Call count: ' . $this->count . PHP_EOL;
        $query->limit($this->limit);
        $this->send($query);
    }

    private function send($query ) {
        $calls = $query->all();
        foreach ($calls as $call) {
            $call->sendToCRM($call->manager[0]);
            $this->sendedCount++;
            //echo  $this->sendedCount .  PHP_EOL;
            if ((int)($this->sendedCount * 100 / $this->count) > $this->percent) {
                $this->percent = (int)($this->sendedCount * 100 / $this->count);
                echo  $this->percent . '%' .  PHP_EOL;
            }
        }
        if ($this->sendedCount < $this->count) {
            $this->send($query);
        }
    }
}