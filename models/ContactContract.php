<?php

namespace app\models;

use Yii;
use yii\db\Query;
use app\components\Notification;

/**
 * This is the model class for table "contact_contract".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $object_id
 * @property string $system_date
 * @property string $price
 * @property string $agreement
 */
class ContactContract extends \yii\db\ActiveRecord {

    public $history_text;
    private $solution_name;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_contract';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'object_id', 'system_date', 'price', 'agreement'], 'required'],
            [['contact_id', 'object_id', 'manager_id', 'fin_dir_id'], 'integer'],
            [['system_date'], 'safe'],
            [['price', 'agreement'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'object_id' => 'Object ID',
            'system_date' => 'System Date',
            'price' => 'Price',
            'agreement' => 'Agreement',
        ];
    }

    public static function getTableColumns() {
        return [
            '`cc`.`id`',
            '`cc`.`system_date`',
            '`c`.`first_name`',
            '`c`.`second_name`',
            '`cc`.`contact_id`',
            '`u`.`firstname` as `manager_name`',
            '`oa`.`link`',
            '`cc`.`price`',
            '`com`.`comment`',
            '`cs`.`name` as `solution`',
        ];
    }

    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    public function getObject() {
        return $this->hasOne(ObjectApartment::className(), ['id' => 'object_id']);
    }

    public function getPayments() {
        return $this->hasMany(ContractPayment::className(), ['contract_id' => 'id']);
    }

    public function add($contact_id, $object_id, $price, $agreement) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->contact_id = $contact_id;
            $this->object_id = $object_id;
            $this->price = $price;
            $this->agreement = $agreement;
            $this->system_date = date('Y-m-d G:i:s', time());
            $this->save();
            $action_type = ActionType::find()->where(['name' => 'contract'])->one();
            $action = new Action();
            $action->add($contact_id, $action_type->id, [$object_id]);
            $contact_history = new ContactHistory();
            $history_text = $this->buildHistory();
            $contact_history->add($contact_id, $history_text, $this->id, 'contract', $this->system_date);
            $this->setHistoryText($history_text);
            $transaction->commit();
            $contact = Contact::find()->select(['first_name', 'second_name'])->where(['id' => $this->contact_id])->one();
            $manager = User::find()->select(['firstname'])->where(['id' => $this->manager_id])->one();
            $object = ObjectApartment::find()->select(['link'])->where(['id' => $this->object_id])->one();
            if (strlen($contact->first_name) > 0) {
                $request_params['contact_name'] = $contact->first_name;
            } else {
                $request_params['contact_name'] = $contact->second_name;
            }
            $request_params['price'] = $price;
            $request_params['link'] = $object->link;
            $request_params['manager'] = $manager->firstname;
            Notification::newContract($request_params);
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            UploadDoc::remove($agreement);
            return false;
        }
    }

    public function edit($object_id, $price, $agreement) {
        try {
            $this->object_id = $object_id;
            $this->price = $price;
            if ($agreement) {
                $this->agreement = $agreement;
            }
            $this->fin_dir_id = null;
            $this->solution_id = null;
            $this->system_date = date('Y-m-d G:i:s', time());
            $this->save();
            $contact_history = new ContactHistory();
            $history_text = $this->buildHistory();
            $contact_history->add($this->contact_id, $history_text, $this->id, 'contract', $this->system_date);
            $this->setHistoryText($history_text);
            $contact = Contact::find()->select(['first_name', 'second_name'])->where(['id' => $this->contact_id])->one();
            $manager = User::find()->select(['firstname'])->where(['id' => $this->manager_id])->one();
            $object = ObjectApartment::find()->select(['link'])->where(['id' => $this->object_id])->one();
            if (strlen($contact->first_name) > 0) {
                $request_params['contact_name'] = $contact->first_name;
            } else {
                $request_params['contact_name'] = $contact->second_name;
            }
            $request_params['price'] = $price;
            $request_params['link'] = $object->link;
            $request_params['manager'] = $manager->firstname;
            Notification::newContract($request_params);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public static function getContractsByContactId($contact_id) {
        $query = new Query();
        $query->select(['`cc`.`id`', '`cs`.`name` as `solution`', '`cc`.`comment`', '`cc`.`solution_id`'])
                ->from(self::tableName() . ' as `cc`')
                ->join('LEFT JOIN', ContractSolution::tableName() . ' `cs`', '`cs`.`id` = `cc`.`solution_id`')
                ->where(['`cc`.`contact_id`' => $contact_id])
                ->orderBy(['id' => SORT_DESC]);
        //->andWhere(['is not', '`cc`.`solution_id`', null]);
        $result = $query->one();
        return $result;
    }

    public function solution($type, $comment = '') {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $solution = ContractSolution::find()->where(['name' => $type])->one();
            $this->fin_dir_id = Yii::$app->user->identity->id;
            $this->solution_id = $solution->id;
            $this->comment = $comment;
            $this->save();
            $this->setSolutionName($solution->name);
            $manager_notify = new ManagerNotification();
            $contact = $this->contact;
            switch ($type) {
                case "approved":
                    $contact->status = 'deal';
                    $contactStatusHistory = new ContactStatusHistory();
                    $contactStatusHistory->add($contact['id'], $this->manager_id, 'deal');
                    $contact->save();
                    $contactStatusHistory->save();
                    $manager_notify->add(date('Y-m-d G:i:s', time()), 'contract_approved', $this->manager_id, null, $contact->id);
                    break;
                case "revision":
                    $manager_notify->add(date('Y-m-d G:i:s', time()), 'contract_revision', $this->manager_id, null, $contact->id);
                    break;
                case "rejected":
                    $manager_notify->add(date('Y-m-d G:i:s', time()), 'contract_rejected', $this->manager_id, null, $contact->id);
                    break;
            }
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    public static function getDetailById($id) {
        $query = new Query();
        $query->select(['`cc`.*, `oa`.`id` as `apartment`,`of`.`id` as `floor`,`oh`.`id` as `house`, `oq`.`id` as `queue`'])
                ->from(self::tableName() . ' as `cc`')
                ->join('LEFT JOIN', ObjectApartment::tableName() . ' `oa`', '`oa`.`id` = `cc`.`object_id`')
                ->join('LEFT JOIN', ObjectFloor::tableName() . ' `of`', '`of`.`id` = `oa`.`floor_id`')
                ->join('LEFT JOIN', ObjectHouse::tableName() . ' `oh`', '`oh`.`id` = `of`.`house_id`')
                ->join('LEFT JOIN', ObjectQueue::tableName() . ' `oq`', '`oq`.`id` = `oh`.`queue_id`')
                ->where(['`cc`.`id`' => $id])
                ->orderBy(['`cc`.`id`' => SORT_ASC]);
        return $query->one();
    }

    private function buildHistory() {
        $history_text = "Отправлено на согласование";
        return $history_text;
    }

    public function setHistoryText($history_text) {
        $this->history_text = $history_text;
    }

    public function setSolutionName($solution_name) {
        $this->solution_name = $solution_name;
    }

    public function getHistoryText() {
        return $this->history_text;
    }

    public function getSolutionName() {
        return $this->solution_name;
    }

}
