<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Contact;
use yii\console\Controller;

class SendAllContactsController extends Controller {

    private $sendedCount = 0;
    private $percent = 0;
    private $count = 0;
    private $limit = 500;

    function actionIndex() {
        set_time_limit(0);
        $query = Contact::find()->where('`contact`.`id` NOT IN (SELECT `contact_id` FROM `fail_export_contacts`) AND `contact`.`sended_crm` = 0');
        $this->count = $query->count();
        echo 'Contact count: ' . $this->count . PHP_EOL;
        $query->limit($this->limit);
        $this->send($query);
    }

    private function send($query) {
        $contacts = $query->all();
        foreach ($contacts as $contact) {
            $contact->sendToCRM();
            $this->sendedCount++;
            if ((int)($this->sendedCount * 100 / $this->count) > $this->percent) {
                $this->percent = (int)($this->sendedCount * 100 / $this->count);
                echo  $this->percent . '%' .  PHP_EOL;
            }
        }

        if ($this->sendedCount < $this->count) {
            $this->send($query, $this->sendedCount);
        }
    }
}