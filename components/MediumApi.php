<?php
namespace app\components;

use app\models\{Contact, ContactsVisits, Departments};
use app\models\helpers\MediumLogsApi;
use yii\db\ActiveRecord;
use yii\httpclient\{Client, Response};
use yii\web\HttpException;

class MediumApi
{
    public $mediumApiDomain;
    public $doctorsVisit;
    public $cabinetSchedule;

    private $minTime;
    private $maxTime;

    const LINK_GET_CONTACT = 'get_contact';
    const LINK_FETCH_CONTACTS = 'fetch_contacts';
    const LINK_PUT_CONTACT = 'put_contact';
    const LINK_GET_VISIT = 'get_visit';
    const LINK_PUT_VISIT = 'put_visit';
    const LINK_DOCTOR_VISIT = 'doctor_visit';
    const LINK_CABINET_SCHEDULE = self::LINK_DOCTOR_VISIT;

    const LINK_SYS_CONTACT_STR = '_contact_visit';
    const LINK_SYS_OID_STR = '_oid_str';

    const SEND_TIMEOUT = 3;

    const DATE_FORMAT = 'Y-m-d\TH:i:s';

    const XML_CONTACT = 'contact';
    const XML_VISIT = 'visit';

    protected $apiFieldsMap = [
        self::XML_CONTACT => [
            'name' => 'FIO',
            'E-mail' => 'Email',
            'ТелефонМоб' => 'Phone',
            'ДатаРождения' => 'Birth',
            'Город' => 'City',
        ],
        self::XML_VISIT => [
            'Пациент' => 'contact',
            'ДатаПриема' => 'date',
            'ВремяПриема' => 'time',
            'Врач' => 'doctor',
            'Кабинет' => 'cabinet',
            'Статус' => 'm_status',
            'Визит' => 'v_status',
            'Комментарий' => 'comment',
            'ИсточникИнформации' => 'attraction_channel',
            'Телефон' => 'phone',
            'Предупреждение' => 'warning',
        ],
    ];

    protected $defaultReplacements;

    protected function getDefaultReplacements()
    {
        if(!isset($this->defaultReplacements)) {
            $this->defaultReplacements = [];
            foreach($this->replacements as $key=>$value) {
                $this->defaultReplacements['{_'.strtoupper($key).'}'] = $value;
            }
            $this->defaultReplacements['{_DOMAIN}'] = $this->mediumApiDomain;
        }

        return $this->defaultReplacements;
    }

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
        $minTime = strtotime($day . ' ' . '08:00');
        $maxTime = strtotime($day . ' ' . '20:00');
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
                . 'attribute Phone {$ob/@Телефон},' . "\n"
                . 'attribute time {$ob/@ВремяПриема}' . "\n"
                . "}";
            $url = $this->link(self::LINK_DOCTOR_VISIT, [
                'DEP_HD' => $apiUrl,
                'day' => $day,
            ]);
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
        $url = $this->link(self::LINK_CABINET_SCHEDULE, [
            'DEP_HD' => $apiUrl,
            'day' => $day,
        ]);
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
                    . 'attribute Status {$ob/@Статус},' . "\n"
                    . 'attribute time {$ob/@ВремяПриема}' . "\n"
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

    public function getContact($oid)
    {
        $url = $this->link(self::LINK_GET_CONTACT, [
            'OID' => $oid
        ]);

        $client = new Client();

        $log = MediumLogsApi::setRequestData($url, '');
        try {
            $response = $client->get($url)->send();
            if($response->getStatusCode() != 200) {
                throw new \Exception('Error querying MediumAPI');
            }
            // There should be only one contact information so fetch it by index
            return $this->parseContactsXml($response->getContent())[0] ?? false;
        } catch(\Exception $e) {
            $log->setResponse('[ERROR] '.$e->getMessage());
            return false;
        }

        return $xml->attributes();
    }

    public function getVisit($oid, $departmentPart)
    {
        $url = $this->link(self::LINK_GET_VISIT, [
            'department' => $departmentPart,
            'oid' => $oid,
        ]);

        try {
            return $this->parseVisitXml($this->get($url)->getContent());
        } catch(\Exception $e) {
            echo $e->getMessage();die;
            return null;
        }
    }

    public function putContact($data, $oid = null)
    {
        $contactData = $this->preparePutContactData($data);
        $contactData['OID_STR'] = $oid?$this->body(self::LINK_SYS_OID_STR, ['oid' => $oid]):'';

        $url = $this->link(self::LINK_PUT_CONTACT);
        $body = $this->body(self::LINK_PUT_CONTACT, $contactData);

        try {
            $response = $this->post($url, $body);

            return $response->getData()[0];
        } catch(\Exception $e) {
            return null;
        }
    }

    public function putVisit(ContactsVisits $visit, $oid = null)
    {
        $visitData = $this->preparePutVisitData($visit);
        $visitData['OID_STR'] = $oid?$this->body(self::LINK_SYS_OID_STR, ['oid' => $oid]):'';

        $department = $visit->getDepartment()->one();
        /**@var $department Departments*/
        if(!$department) {
            throw new \Exception('Can not fid department for visit #'.$visit->id);
        }

        $url = $this->link(self::LINK_PUT_VISIT, [
            'host' => $department->api_send_url,
            'url' => $department->api_url,
        ]);
        $body = $this->body(self::LINK_PUT_VISIT, $visitData);

        try {
            $response = $this->post($url, $body);

            return $response->getData()[0];
        } catch(\Exception $e) {
            return null;
        }
    }

    public function preparePutContactData($source)
    {
        if($source instanceof ActiveRecord) {
            $source = $source->attributes;
        }

        $birthdayString = '';
        if($source['birthday']) {
            $birthdayString = \DateTime::createFromFormat('Y-m-d', $source['birthday'])
                ->format(self::DATE_FORMAT);
        }

        return [
            'name' => implode(' ', [
                $source['surname'],
                $source['name'],
                $source['middle_name'],
            ]),
            'birthday' => $birthdayString,
            'phone' => $source['first_phone'] ?? '',
            'email' => $source['first_email'] ?? '',
            'city' => $source['city'] ?? '',
            'attraction_channel' => $source['attraction_channel_id'] ?? '',
        ];
    }

    protected function preparePutVisitData(ContactsVisits $visit)
    {
        $contact = $visit->getContact()->one();
        /**@var $contact Contact*/
        if(!$contact) {
            throw new \Exception('Visit contact should be filled');
        }

        $attrChannel = $contact->getAttractionChannel()->one();
        $infoSource = $attrChannel?$attrChannel->name:'';

        $type = 'Новый';
        $contactStr = '';
        if($contact->status == Contact::CONTACT) {
            $type = 'Повторный';
            if($contact->medium_oid) {
                $contactStr = $this->body(self::LINK_SYS_CONTACT_STR, [
                    'oid' => $contact->medium_oid,
                ]);
            }
        }

        $visitDate = \DateTime::createFromFormat(ContactsVisits::DATE_FORMAT, $visit->visit_date);

        return [
            'DATE' => $visitDate->format(self::DATE_FORMAT),
            'TIME' => $visit->time,
            'NAME' => $contact->getFullName(),
            'DOC_NAME' => $visit->doctor_name,
            'CAB_NAME' => $visit->cabinet_name,
            'TYPE' => $type,
            'COMMENT' => $visit->comment,
            'ATTRACTION_CHANNEL' => $infoSource,
            'PHONE' => $contact->first_phone,
            'CONTACT' => $contactStr,
            'DOC_ID' => $visit->doctor_oid,
            'CAB_ID' => $visit->cabinet_oid,
        ];
    }

    public function fetchContactsToSync(\DateTime $from, \DateTime $till)
    {
        $url = $this->link(self::LINK_FETCH_CONTACTS);
        $data = $this->body(self::LINK_FETCH_CONTACTS, [
            'DATE_FROM' => $this->dateString($from),
            'DATE_TO' => $this->dateString($till),
        ]);

        $log = MediumLogsApi::setRequestData($url, $data);

        $response = (new Client())
                        ->createRequest()
                            ->addHeaders(['Content-type' => 'application/x-www-form-urlencoded'])
                            ->setUrl($url)
                            ->setContent($data)
                        ->send();
        $response->setFormat(Client::FORMAT_XML);
        $log->setResponse($response->getContent());

        return $this->parseContactsXml($response->getContent());
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

    public function parseContactsXml(string $xml)
    {
        return $this->parseXml($xml, self::XML_CONTACT);
    }

    public function parseVisitXml(string $xml)
    {
        return $this->parseXml($xml, self::XML_VISIT);
    }

    public function parseXml(string $xml, $type): array
    {
        if(false !== strpos($xml, '<?xml')) {
            // Remove XML comment to be able to correctly parse XML string
            $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        }
        $oXml = new \SimpleXMLElement('<xml>'.$xml.'</xml>');

        if($oXml) {
            $list = [];
            foreach($oXml->xpath('OBJECT') as $xmlItem) {
                $list[count($list)] = $this->parseXmlItem($xmlItem, $type);
            }
            return $list;
        } else {
            return [];
        }
    }

    private function parseXmlItem(\SimpleXMLElement $element, $type): array
    {
        $data = [];
        foreach($element->attributes() as $name=>$value) {
            $data[$this->mapFieldName($name, $type)] = (string) $value;
        }
        //$data['@children'] = [];
        foreach($element->children() as $child) {
            $elName = '@'.$this->mapFieldName($child->getName(), $type);
            $data[$elName] = $data[$elName] ?? [];
            $data[$elName][] = $this->parseXmlItem($child, $type);
        }
        return $data;
    }

    private function get($url, $body = '', $saveLogs = true): Response
    {
        return $this->doReuest('get', $url, $body, $saveLogs);
    }

    private function post($url, $body, $saveLogs = true): Response
    {
        return $this->doReuest('post', $url, $body, $saveLogs);
    }

    private function doReuest($type, $url, $body, $saveLogs = true): Response
    {
        if($saveLogs) {
            $log = MediumLogsApi::setRequestData($url, $body);
        }

        try {
            $response = (new Client([
                'requestConfig' => [
                    'format' => Client::FORMAT_XML,
                    'headers' => ['Content-type' => 'application/x-www-form-urlencoded'],
                    'options' => [
                        'timeout' => self::SEND_TIMEOUT,
                    ],
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_XML
                ]
            ]))
                ->{strtolower($type)}($url, $body)
                ->send();
            if($saveLogs) {
                $log->setResponse($response->getContent());
            }

            if(!$response->isOk) {
                throw new \Exception('Invalid response. '.$response->getContent());
            }

            return $response;
        } catch(\Exception $e) {
            if($saveLogs) {
                $log->setResponse('Error: '.$e->getMessage());
            }

            throw new \Exception('Invalid request.');
        }
    }

    private function link($name, array $replacements = [])
    {
        $raw = $this->links[$name] ?? null;
        if(!$raw) {
            throw new \Exception('Link with type "'.$name.'" isn\'t configured.');
        }

        return $this->replace($raw, $replacements);
    }

    private function body($name, array $replacements = [])
    {
        $raw = $this->bodies[$name] ?? null;
        if(!$raw) {
            throw new \Exception('Body with type "'.$name.'" isn\'t configured.');
        }

        return $this->replace($raw, $replacements);
    }

    private function replace(string $source, array $replacements = [])
    {
        $mergedReplacements = $this->getDefaultReplacements();
        foreach($replacements as $key=>$value) {
            // Overwrite default settings if needed
            $mergedReplacements['{'.strtoupper($key).'}'] = $value;
        }

        return str_replace(
            array_keys($mergedReplacements),
            array_values($mergedReplacements),
            $source
        );
    }

    private function dateString(\DateTime $date)
    {
        return $date->format(self::DATE_FORMAT);
    }

    private function mapFieldName(string $name, $type = self::XML_CONTACT)
    {
        $map = $this->apiFieldsMap[$type] ?? [];

        return $map[$name] ?? $name;
    }
}