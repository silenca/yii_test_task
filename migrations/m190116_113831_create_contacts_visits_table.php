<?php

use yii\db\Migration;
use app\models\ContactsVisits;

/**
 * Handles the creation of table `contacts_visits`.
 */
class m190116_113831_create_contacts_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contacts_visits', [
            'id' => $this->primaryKey(),
            'create_date' => $this->dateTime(),
            'edit_date' => $this->dateTime(),
            'visit_date' => $this->dateTime(),
            'contact_id' => $this->integer('11'),
            'department_id' => $this->integer('11'),
            'medium_oid' => $this->string()->notNull(),
            'status' => $this->integer(),
        ]);

        // creates index for column `contact_id`
        $this->createIndex(
            'idx-contacts_visits-contact_id',
            'contacts_visits',
            'contact_id'
        );

        // add foreign key for table `contact`
        $this->addForeignKey(
            'fk-contacts_visits-contact_id',
            'contacts_visits',
            'contact_id',
            'contact',
            'id',
            'CASCADE'
        );

        // creates index for column `contact_id`
        $this->createIndex(
            'idx-contacts_visits-department_id',
            'contacts_visits',
            'department_id'
        );

        // add foreign key for table `contact`
        $this->addForeignKey(
            'fk-contacts_visits-department_id',
            'contacts_visits',
            'department_id',
            'departments',
            'id',
            'CASCADE'
        );

        $logVisits = \app\models\ContactVisitLog::find()->all();
        if($logVisits){
            foreach ($logVisits as $logVisit){
                $visit = new ContactsVisits();
                $visit->create_date = $logVisit->date;
                $visit->edit_date = $logVisit->date;
                $visit->visit_date = $logVisit->date_visit;
                $visit->contact_id = $logVisit->contact_id;
                $visit->medium_oid = $logVisit->medium_oid;
                $visit->department_id = 1;
                $visit->status = ContactsVisits::STATUS_PENDING;
                $visit->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contacts_visits');
    }
}
