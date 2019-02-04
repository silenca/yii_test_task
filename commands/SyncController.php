<?php
namespace app\commands;

use app\components\MediumApi;
use app\controllers\ContactsController;
use app\models\{
    Contact, ContactsVisits, Speciality, Vars
};
use app\models\helpers\MediumLogsApi;
use yii\httpclient\{
    Client, Exception as ClientException, Request, Response, XmlFormatter, XmlParser
};
use yii\console\Controller;
use Yii;

class SyncController extends Controller
{
    const VAR_LAST_SYNC_TS = 'lastSyncTs';

    public function actionMedium()
    {
        $lastSync = Vars::get(self::VAR_LAST_SYNC_TS, 0);

        if(!$lastSync) {
            $dateFrom = (new \DateTime())->modify('- 2 minutes');
        } else {
            $dateFrom = (new \DateTime())->setTimestamp($lastSync);
        }
        $dateTo = (new \DateTime());
        $lastSync = $dateTo->format('U') - 1;

        try {
            $contacts = Yii::$app->medium->fetchContactsToSync($dateFrom, $dateTo);
            if(count($contacts)) {
                foreach($contacts as $contact) {
                    ContactsController::updateContact($contact);
                }
            } else {
                echo 'No data on Medium';
            }

            Vars::set(self::VAR_LAST_SYNC_TS, $lastSync);
        } catch(ClientException $ex) {
            echo implode(PHP_EOL, [
                'Error quering medium',
                $ex->getMessage(),
                $ex->getTraceAsString(),
            ]).PHP_EOL.PHP_EOL;
        }
    }

    public function actionVisitSend()
    {
        $visits = ContactsVisits::fetchToSync();
        foreach($visits as $visit) {
            try {
                $visitOid = Yii::$app->medium->putVisit($visit);

                if(!$visitOid) {
                    throw new \Exception('There is no OID in PUT_VIST response');
                }

                $visit->medium_oid = $visitOid;

                $contact = $visit->getContact()->one();
                /**@var $contact Contact*/
                if(!$contact) {
                    throw new \Exception('Can not find contact for visit #'.$visit->id);
                }

                $visit->sync_status = ContactsVisits::SYNC_STATUS_SYNCED;
                if($contact->status == Contact::LEAD) {
                    // We should wait until contact will be created on medium to prevent duplication
                    $visit->sync_status = ContactsVisits::SYNC_STATUS_WAITING;
                }

                $visit->save();
            } catch(\Exception $e) {
                echo '[VISIT SYNC] [#'.$visit->id.'] [ERROR] '.$e->getMessage()."\r\n";
            }
        }
    }

    public function actionVisitUpdateLead()
    {
        $visits = ContactsVisits::fetchToUpdateLead();
        foreach($visits as $visit) {
            /**@var $visit ContactsVisits*/
            try {
                $department = $visit->getDepartment()->one();
                if(!$department) {
                    throw new \Exception('Can not find department for visit #'.$visit->id);
                }
                try {
                    $visitData = Yii::$app->medium->getVisit($visit->medium_oid, $department->api_url);
                } catch(\Exception $e) {
                    echo $e->getMessage();die;
                }

                $contactOid = $this->fetchContactOidFromVisitData($visitData);
                if(!$contactOid) {
                    continue;
                }

                $contact = Contact::findOne(['medium_oid' => $contactOid]);
                if (!$contact) {
                    throw new \Exception('Can not find synced contact. Waiting ...');
                }

                $lead = $visit->getContact()->one();
                if (!$lead) {
                    throw new \Exception('Can not find lead for visit #' . $visit->id);
                }

                try {
                    $contact = Contact::merge($lead, $contact);
                } catch(\Exception $e) {
                    throw new \Exception('Can not merge contacts. '.$e->getMessage());
                }

                // Update visit SYNC status
                $visit->contact_id = $contact->id;
                $visit->sync_status = ContactsVisits::SYNC_STATUS_SYNCED;

                if(!$visit->save()) {
                    throw new \Exception('Error saving visit. '.implode('; ', $visit->getErrorSummary(true)));
                }
            } catch(\Exception $e) {
                // @TODO: Add logger
                echo '[ERROR] '.$e->getMessage()."\r\n";
            }
        }
    }

    public function actionSpeciality()
    {
        $cnt = 0;
        $speciality = Yii::$app->medium->speciality();
        if(empty($speciality['error'])){
            foreach ($speciality['data'] as $datum){
                if(Speciality::import($datum)){
                    $cnt++;
                }else{
                    echo "ERROR SAVE " .  print_r($datum) . "\n";
                }
            }

            echo "SAVE|UPDATE {$cnt} items \n";
        }else{
            echo $speciality['error'];
        }
    }

    protected function fetchContactOidFromVisitData(array $visit)
    {
        $contactLink = $visit[0]['@contact'][0]['link'] ?? null;
        if(!$contactLink) {
            return null;
        }

        $matches = [];
        if(!preg_match('/\/O:(.+)$/', $contactLink, $matches)) {
            return null;
        }

        return $matches[1];
    }
}