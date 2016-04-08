<?php
/**
 * Created by PhpStorm.
 * User: phobos
 * Date: 11/9/15
 * Time: 5:25 PM
 */

namespace app\components\widgets;


use yii\base\Widget;


class ReportsWidget extends Widget
{
    public $reports;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->reports as $report) {

            if (!array_key_exists($report['id'], $data)) {
                $data[$report['id']]['id'] = $report['id'];
                $data[$report['id']]['name'] = $report['name'];
                $data[$report['id']]['incoming'] = 0;
                $data[$report['id']]['outgoing'] = 0;
                $data[$report['id']]['lead'] = 0;
            }
            $column_name = $report['selector'];
            $data[$report['id']][$column_name] = $report['count'];
        }

        // Вывести количество успешных звонков
        foreach ($data as &$item) {
            // Исходящих
            if (isset($item['answered_incoming'])) {
                $item['incoming'] = $item['answered_incoming'] . ' / ' . $item['incoming'];
            } else {
                $item['incoming'] = 0 . ' / ' . $item['incoming'];
            }

            // Входящих
            if (isset($item['answered_outgoing'])) {
                $item['outgoing'] = $item['answered_outgoing'] . ' / ' . $item['outgoing'];
            } else {
                $item['outgoing'] = 0 . ' / ' . $item['outgoing'];
            }
        }

        $dataObjects = Array();
        $i = 0;
        foreach ($data as $dataItem) {
            foreach ($dataItem as $j => $dataItemItem) {
                switch ($j) {
                    case "id":
                        break;
                    default:
                        $dataObjects[$i][] = $dataItemItem;
                        break;
                }
            }
            $i++;
        }
        return $dataObjects;
    }

}