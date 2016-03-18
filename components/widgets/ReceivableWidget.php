<?php

namespace app\components\widgets;

use yii\base\Widget;

class ReceivableWidget extends Widget {

    public $receivables;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        $ids = [];
        $i = 0;
        foreach ($this->receivables as $receivable) {
            if (($index = array_search($receivable['id'], $ids)) !== false) {
                $data[$index]['payments'][] = $receivable['amount'];
                $data[$index][4]['payments'][$receivable['payment_id']]['system_date'] = $receivable['system_date'];
                $data[$index][4]['payments'][$receivable['payment_id']]['amount'] = $receivable['amount'];
                $data[$index][4]['whole_amount'] += $receivable['amount'];
                $data[$index][5] -= $receivable['amount'];
            } else {
                $ids[$i] = $receivable['id'];
                $data[$i][0] = $receivable['id'];
                $data[$i][1] = "<a class='contact' href='/contacts#contact=" . $receivable['contact_id'] . "/'>" . $receivable['first_name'] . "</a>";
                $data[$i][2] = "<a target='_blank' href='" . $receivable['link'] . "'>" . $receivable['link']."</a>";
                $data[$i][3] = $receivable['price'];
                $data[$i][4]['whole_amount'] = 0;
                $data[$i][5] = $receivable['price'];               
                $data[$i][6] = $receivable['firstname'];
                if (isset($receivable['amount'])) {
                    $data[$i]['payments'][] = $receivable['amount'];
                    $data[$i][4]['whole_amount'] = $receivable['amount'];
                    $data[$i][4]['payments'][$receivable['payment_id']]['system_date'] = $receivable['system_date'];
                    $data[$i][4]['payments'][$receivable['payment_id']]['amount'] = $receivable['amount'];
                    $data[$i][5] -= $receivable['amount'];
                }
                $i++;
            }
        }
        return $data;
    }

}
