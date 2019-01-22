<?php

namespace app\components;

use app\models\Contact;
use app\models\helpers\MediumLogsApi;
use yii\httpclient\Client;
use yii\web\HttpException;

class MediumApi
{
    public $mediumApiDomain;
    public $doctorsVisit;
    public $cabinetSchedule;

    private $minTime;
    private $maxTime;

    public const CONTACT_URL = 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1D45F18F27C737D';
    public const KEY_OBJECT = '/O:';

    private function sendMedium($url, $data)
    {
        $log = MediumLogsApi::setRequestData($url, $data);
        $client = new Client();
        $request = $client->createRequest();
        $request->setUrl($url);
        $request->setData($data);
        $send = $request->send();
        $log->setResponse($send->getContent());
        return $send;
    }

    private function sendMediumPost($url, $data)
    {
        $log = MediumLogsApi::setRequestData($url, $data);
        $client = new Client();
        $send = $client->post($url, $data)->send();
        $log->setResponse($send->getContent());
        return $send;
    }

    private function setMinMaxTime($doctor, $day)
    {
        if(!empty($doctor['gr'])){
            $schedule = explode('-', $doctor['gr']);
            if(!$this->minTime || strtotime($day . ' ' . $schedule[0]) < $this->minTime){
                $this->minTime = strtotime($day . ' ' . $schedule[0]);
            }

            if(!$this->maxTime || $this->maxTime < strtotime($day . ' ' . $schedule[1])){
                $this->maxTime = strtotime($day . ' ' . $schedule[1]);
            }
        }
    }

    private function setTimeCabinet($day): array
    {
        $grTime = [];
        $minTime = strtotime($day . ' ' . '07:00');
        $maxTime = strtotime($day . ' ' . '21:00');
        $nextMinutes = 30 * 60;
        $grTime[$minTime]['time'] = date("H:i", $minTime);
        for($minutes = $nextMinutes; $minTime + $minutes <= $maxTime;){
            $time = $minTime + $minutes;
            $grTime[$time]['time'] = date("H:i", $time);
            $grTime[$time]['class'] = '';
            $minutes = $minutes + $nextMinutes;
        }

        unset($minTime, $maxTime, $nextMinutes, $minutes, $time);

        return $grTime;
    }

    /**
     * Заполняем время прийома врача
     * @param $doctors
     * @return array
     */
    private function parseScheduleDoctors($doctors, $day): array
    {
        if(!empty($doctors)){
            $nextMinutes = 30 * 60;
            foreach ($doctors as $idDoctor=>$doctor){
                if(!empty($doctor['gr'])){
                    $schedule = explode('-', $doctor['gr']);
                    $startTime = strtotime($day . ' ' . $schedule[0]);
                    $stopTime = strtotime($day . ' ' . $schedule[1]);
                    $doctors[$idDoctor]['gr_time'][$this->minTime]['time'] = date("H:i", $this->minTime);
                    $doctors[$idDoctor]['gr_time'][$this->minTime]['class'] = ($startTime <= $this->minTime && $this->minTime < $stopTime ? '':'empty');
                    for($minutes = $nextMinutes; $this->minTime + $minutes <= $this->maxTime;){
                        $time = $this->minTime + $minutes;
                        $doctors[$idDoctor]['gr_time'][$time]['time'] = date("H:i", $time);
                        $doctors[$idDoctor]['gr_time'][$time]['class'] = ($startTime <= $time && $time < $stopTime ? '':'empty');
                        $minutes = $minutes + $nextMinutes;
                    }
                }
            }
        }

        unset($nextMinutes, $idDoctor, $doctor, $schedule, $day, $startTime, $stopTime, $minutes, $time);
        return $doctors;
    }

    /**
     * Получаем специализации
     * @return array
     */
    public function speciality(): array
    {
        $result = ['data'=>[],'error'=>''];
        try {
            $response = $this->sendMedium($this->mediumApiDomain . '/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1CDA3C2A65EAEF7/I:PACK',
                'for $ob in PACK/OBJECT '
                            .'return element OBJECT '
                            .'{ '
                            .'attribute oid { $ob/@oid }, '
                            .'attribute name { $ob/@name } '
                            .'}');
            if(!empty($response->getData())){
                foreach ($response->getData()['OBJECT'] as $content) {
                    if(!empty($content['@attributes'])){
                        $result['data'][] = $content['@attributes'];
                    }elseif(!empty($content['attributes'])){
                        $result['data'][] = $content['attributes'];
                    }else{
                        $result['data'][] = $content;
                    }
                }
            }else{
                $result['error'] = 'No data on Medium';
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex;
        }
        return $result;
    }


    /**
     * Список доктаров
     * @param $apiUrl
     * @param $day
     * @param $specialization
     * @return array
     */
    public function doctorsSchedule($apiUrl, $day, $specialization): array
    {
        $result = ['data'=>[],'error'=>''];
        try {
            $data = 'let $day := \'' . $day . '\' '
                . 'let $oidOtdela := \'' . $specialization . '\' '
                . 'let $Mask := oda:left($day,7) '
                . 'let $Path := "' . $apiUrl . '/C:1D1C22EBEED58C8/I:PACK|" '
                . 'let $Resp := oda:xquery-doc(concat($Path,$Mask),"*") '
                . 'let $res := '
                . '(for $objDoctor in PACK/OBJECT[oda:right(Отделения/Отдел/@link,15)=$oidOtdela] '
                . 'let $Obj := $Resp/PACK/OBJECT[Сотрудник/@oid=$objDoctor/@oid] '
                . 'return element OBJECT { '
                . 'attribute oid { $objDoctor/@oid }, '
                . 'attribute name { $objDoctor/@name }, '
                . 'attribute gr {$Obj/Расп[oda:left(@Дата,10)=$day]/concat(substring(@С, 12, 5),\' - \',substring(@По, 12, 5))}}) '
                . 'return element x { $res[@gr] }';
            
            $response = $this->sendMediumPost($this->mediumApiDomain . "/api/" . $apiUrl . "/C:1CDCBA80BCACD1E/I:PACK", $data);
            if(!empty($response->getContent()) && !empty($response->getData())){
                foreach ($response->getData()['OBJECT'] as $content) {
                    if(!empty($content['@attributes'])){
                        $this->setMinMaxTime($content['@attributes'], $day);
                        $result['data'][$content['@attributes']['oid']] = $content['@attributes'];
                    }elseif(!empty($content['attributes'])){
                        $this->setMinMaxTime($content['attributes'], $day);
                        $result['data'][$content['attributes']['oid']] = $content['attributes'];
                    }else{
                        $this->setMinMaxTime($content, $day);
                        $result['data'][$content['oid']] = $content;
                    }
                }
                $result['data'] = $this->parseScheduleDoctors($result['data'], $day);
            }else{
                $result['error'] = 'No data on Medium doctors schedule';
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex;
        }
        return $result;
    }

    /**
     * Список забронированих сиансов к доктору
     * @param $apiUrl
     * @param $day
     * @return array
     */
    public function doctorsVisit($apiUrl, $day): array
    {
        $result = ['data'=>[],'error'=>''];
        try {
            $data = 'let $day := "' . $day . '"' . "\n"
                . 'for $ob in //PACK/OBJECT[oda:left(@ДатаПриема,10) = $day ]' . "\n"
                . 'return element OBJECT' . "\n"
                . '{' . "\n"
                . 'attribute oidP { oda:right($ob/Пациент/@link,15) },' . "\n"
                . 'attribute oidV { oda:right($ob/Врач/@link,15) },' . "\n"
                . 'attribute oidK { oda:right($ob/Кабинет/@link,15) },' . "\n"
                . 'attribute date { $ob/@ДатаПриема },' . "\n"
                . 'attribute Patient {$ob/@Пациент},' . "\n"
                . 'attribute Phone {$ob/@Телефон}' . "\n"
                . "}";
            $response = $this->sendMediumPost($this->mediumApiDomain .'/api/' . $apiUrl . $this->doctorsVisit . $day, $data);
            if(!empty($response->getContent()) && mb_strcut($response->getContent(), 0, 2) != "<x>"){
                $response->setContent("<x>".$response->getContent()."</x>");
            }
            if(!empty($response->getContent()) && !empty($response->getData())){
                foreach ($response->getData()['OBJECT'] as $content) {
                    if(!empty($content['@attributes'])){
                        $result['data'][] = $content['@attributes'];
                    }elseif(!empty($content['attributes'])){
                        $result['data'][] = $content['attributes'];
                    }else{
                        $result['data'][] = $content;
                    }
                }
            }else{
                $result['error'] = 'No data on Medium doctors visit';
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex;
        }
        return $result;
    }

    /**
     * Список кабинетов
     * @param $apiUrl
     * @param $day
     * @return array
     */
    public function cabinetList($apiUrl, $day): array
    {
        $url = $this->mediumApiDomain . '/api/' . $apiUrl . '/C:1CE311BD5A26671/I:PACK';
        $result = ['data'=>[],'error'=>''];
        try {
            $data = 'for $ob in PACK/OBJECT' . "\n"
                . 'return element OBJECT' . "\n"
                . '{' . "\n"
                . 'attribute oid { $ob/@oid },' . "\n"
                . 'attribute name { $ob/@name }' . "\n"
                . '}';
            $response = $this->sendMediumPost($url, $data);
            if(!empty($response->getContent()) && mb_strcut($response->getContent(), 0, 2) != "<x>"){
                $response->setContent("<x>".$response->getContent()."</x>");
            }
            if(!empty($response->getContent()) && !empty($response->getData())){
                foreach ($response->getData()['OBJECT'] as $content) {
                    if(!empty($content['@attributes'])){
                        $result['data'][$content['@attributes']['oid']] = $content['@attributes'];
                        $result['data'][$content['@attributes']['oid']]['gr_time'] = $this->setTimeCabinet($day);
                    }elseif(!empty($content['attributes'])){
                        $result['data'][$content['attributes']['oid']] = $content['attributes'];
                        $result['data'][$content['attributes']['oid']]['gr_time'] = $this->setTimeCabinet($day);
                    }else{
                        $result['data'][$content['oidK']] = $content;
                        $result['data'][$content['oid']]['gr_time'] = $this->setTimeCabinet($day);
                    }
                }
            }else{
                $result['error'] = 'No data on Medium cabinet list';
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex;
        }
        return $result;
    }

    /**
     * Список забронированых кабинетов
     * @param $apiUrl
     * @param $day
     * @return array
     */
    public function cabinetSchedule($apiUrl, $day): array
    {
        $url = $this->mediumApiDomain . '/api/' . $apiUrl . $this->cabinetSchedule . $day;
        $result = ['data'=>[],'error'=>''];
        try {
            $data = 'let $data := "' . $day . '"' . "\n"
                    . 'for $ob in //PACK/OBJECT[oda:left(@ДатаПриема,10) = $data and (@Статус="В ожидании")]' . "\n"
                    . 'return element OBJECT' . "\n"
                    . '{' . "\n"
                    . 'attribute oidK { oda:right($ob/Кабинет/@link,15) },' . "\n"
                    . 'attribute oidVizita { $ob/@oid },' . "\n"
                    . 'attribute date { $ob/@ДатаПриема },' . "\n"
                    . 'attribute Cabinet {$ob/@Кабинет},' . "\n"
                    . 'attribute Status {$ob/@Статус}' . "\n"
                    . '}';
            $response = $this->sendMediumPost($url, $data);
            if(!empty($response->getContent()) && mb_strcut($response->getContent(), 0, 2) != "<x>"){
                $response->setContent("<x>".$response->getContent()."</x>");
            }
            if(!empty($response->getContent()) && !empty($response->getData())){
                foreach ($response->getData()['OBJECT'] as $content) {
                    if(!empty($content['@attributes'])){
                        $result['data'][] = $content['@attributes'];
                    }elseif(!empty($content['attributes'])){
                        $result['data'][] = $content['attributes'];
                    }else{
                        $result['data'][] = $content;
                    }
                }
            }else{
                $result['error'] = 'No data on Medium cabinet schedule';
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex;
        }
        return $result;
    }

    public function records($url, $data)
    {
        $result = ['data'=>['response'=>'','oid'=>''],'error'=>''];
        try {
            $response = $this->sendMediumPost($url, $data);
            $result['data']['response'] = $response->getContent();
            if(!empty($response->getContent()) && !empty($response->getData()[0])){
                $result['data']['oid'] = $response->getData()[0];
            }else{
                $result['error'] = 'No data on Medium records URL= "' . $url . '"';
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex;
        }
        return $result;
    }

    public function visitStatus($url, $mediumId)
    {
        $result = ['data'=>'','error'=>''];
        try{
            $url = $this->mediumApiDomain . "/api/" . $url . "/C:1D45F000F6704C0/O:" . $mediumId;
            $response = $this->sendMedium($url, '');
            if($response->statusCode == '200' && !empty($response->getData()['@attributes']['Статус'])){
                $result['data'] = $response->getData()['@attributes']['Статус'];
            }
        } catch (HttpException $ex) {
            $result['error'] = $ex->getMessage();
        }
        return $result;
    }

    public static function getContact($oid)
    {
        $url = implode('', [self::CONTACT_URL, self::KEY_OBJECT, $oid]);

        $client = new Client();

        $log = MediumLogsApi::setRequestData($url, '');
        try {
            $response = $client->get($url)->send();
            if($response->getStatusCode() != 200) {
                throw new \Exception('Error querying MediumAPI');
            }
            // There should be only one contact information so fetch it by index
            return self::parseContactsXml($response->getContent())[0] ?? false;
        } catch(\Exception $e) {
            $log->setResponse('[ERROR] '.$e->getMessage());
            return false;
        }

        return $xml->attributes();
    }

    public static function isUpToDate(Contact $contact, array $mediumData): bool
    {
        $lastSyncDate = strtotime($contact->lastSyncDate);
        $mediumUpdate = strtotime($mediumData['update']);

        if(!$lastSyncDate || !$mediumUpdate) {
            return false;
        }

        if($lastSyncDate < $mediumUpdate) {
            return false;
        }

        return true;
    }

    public static function parseContactsXml(string $xml)
    {
        if(false !== strpos($xml, '<?xml')) {
            // Remove XML comment to be able to correctly parse XML string
            $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        }
        $oXml = new \SimpleXMLElement('<xml>'.$xml.'</xml>');

        if($oXml) {
            return array_reduce($oXml->xpath('OBJECT'), function($list, \SimpleXMLElement $el){
                $idx = count($list);
                foreach($el->attributes() as $name=>$value) {
                    $list[$idx][$name] = (string) $value;
                }
                return $list;
            }, []);
        } else {
            return [];
        }
    }
}