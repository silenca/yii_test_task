<?php
namespace app\models\helpers;

use yii\base\Model;

class MediumLogsApi extends Model
{
    private $startTime;
    private $stopTime;
    private $url;
    private $requestData;
    private $responseData;

    /**
     * @param mixed $requestData
     */
    public static function setRequestData($url, $requestData)
    {
        $model = new MediumLogsApi();
        $model->url = $url;
        $model->requestData = $requestData;
        $model->startTime = microtime(true);
        return $model;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($responseData)
    {
        $this->responseData = $responseData;
        $this->stopTime = microtime(true);
        $this->processLogs();
        $this->deleteOldFiles();
    }

    protected function processLogs()
    {
        $start = $this->startTime;
        $stop = $this->stopTime;
        $sqlLog = "========LOG Medium-" . date("Y-m-d G:i:s") . "\n";
        $sqlLog .= "++++++++++[url]" . $this->url . "\n";
        $sqlLog .= "++++++++++[time]" . ($stop - $start) . "\n";
        $sqlLog .= "++++++++++[request]" . $this->requestData . "\n";
        $sqlLog .= "++++++++++[response]" . $this->responseData . "\n";
        $sqlLog .= "========END LOG Medium\n";

        $filePath = \Yii::$app->basePath . "/runtime/logs/";
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $logFile = $filePath . "mediumLogsApi_" . date("Ymd") . ".log";
        $fp = @fopen($logFile, 'a');
        @flock($fp, LOCK_EX);
        @fputs($fp, $sqlLog);
        @fputs($fp, "\n\n");
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }

    protected function deleteOldFiles()
    {
        $files = glob($filePath = \Yii::$app->basePath . "/runtime/logs/mediumLogsApi_*");
        $now   = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { // 7 days
                    unlink($file);
                }
            }
        }
    }
}