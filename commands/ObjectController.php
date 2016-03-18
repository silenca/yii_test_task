<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\models\ObjectFloor;
use app\models\ObjectApartment;
use app\models\ObjectQueue;
use app\models\ObjectHouse;
use yii\db\Query;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ObjectController extends Controller {

    protected function getQueueID($object)
    {
        $object_queue = ObjectQueue::find()->where(['queue' => $object->kompleks])->one();
        if (!$object_queue) {
            $object_queue = new ObjectQueue();
            $object_queue->queue = $object->kompleks;
            $object_queue->save();
        }
        return $object_queue->id;
    }

    protected function getHouseID($object)
    {
        $object_house = ObjectHouse::find()->where(['housing' => $object->house])->one();
        if (!$object_house && $object->house) {
            $object_house = new ObjectHouse();
            $object_house->housing = $object->house;
            $object_house->queue_id = $this->getQueueID($object);
            $object_house->save();
        }
        return $object_house->id;
    }

    protected function getFloorID($object)
    {
        $object_floor = ObjectFloor::find()->where(['floor' => $object->floor])->one();
        if (!$object_floor && $object->floor) {
            $object_floor = new ObjectFloor();
            $object_floor->floor = $object->floor;
            $object_floor->house_id = $this->getHouseID($object);
            $object_floor->save();
        }
        return $object_floor->id;
    }

    protected function buildData($objects) {
        $existing_apartments = ObjectApartment::find()->select('foreign_id')->all();
        $existing_foreign_ids = [];
        foreach ($existing_apartments as $object) {
            if ($object->foreign_id)
                $existing_foreign_ids[] = $object->foreign_id;
        }
        $data = Array();
        $i = 0;
        foreach ($objects as $object) {
            if (!in_array($object->id, $existing_foreign_ids)) {
                if ($object->kompleks && $object->house && $object->name && $object->floor) {
                    $data[$i][] = $this->getFloorID($object);
                    $data[$i][] = $object->name;
                    $data[$i][] = trim($object->planirovka_area);
                    $data[$i][] = $object->prodano;
                    $data[$i][] = $object->planirovka;
                    $data[$i][] = $object->url;
                    $data[$i][] = $object->id;
                    $i++;
                }
            }
        }
        return $data;
    }

    public function actionParser()
    {
        $url = "http://orangepark.ua/api/all_flat.php?format=json";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $objects_json = curl_exec($curl);
        $objects = (array)json_decode($objects_json);

        $data = $this->buildData($objects);
        if (count($data)) {
            $population = new Query();
            $population->createCommand()->batchInsert(
                ObjectApartment::tableName(),
                ['floor_id','number','area','is_sold','layout','link','foreign_id'],
                $data)->execute();
        }

    }

}
