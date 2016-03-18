<?php

namespace app\components\widgets;

use yii\base\Widget;

class ContractTableWidget extends Widget {

    public $contracts;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->contracts as $i => $contract) {
            $data[$i][] = $contract['id'];
            $data[$i][] = date("d-m-Y G:i:s", strtotime($contract['system_date']));
            $contact_name;
            if ($contract['first_name']) {
                $contact_name = $contract['first_name'];
            } else {
                $contact_name = $contract['second_name'];
            }
            
            $data[$i][] = "<a class='contact' href='/contacts#contact=" . $contract['contact_id'] . "/'>" . $contact_name . "</a>";
            $data[$i][] = $contract['manager_name'];
            $data[$i][] = "<a href='" . $contract['link'] . "' target='_blank'>" . $contract['link'] . "</a>";
            $data[$i][] = $contract['price'];
            $data[$i][] = $contract['comment'];
            switch($contract['solution']) {
                case "approved":
                    $data[$i][] = "<b>Одобрено</b>";
                    break;
                case "revision":
                    $data[$i][] = "<b>Доработать</b>";
                    break;
                case "rejected":
                    $data[$i][] = "<b>Отклонено</b>";
                    break;
                default :
                    $data[$i][] = "none";
                    break;
            }
        }
        return $data;
    }

}
