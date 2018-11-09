<?php
namespace app\components;
use SimpleXMLElement;

class SoapParser extends \yii\web\Request
{
    /* @var \SimpleXMLElement $request */
    protected $request;
    /* @var \SimpleXMLElement $function */
    protected $function;
    /* @var \SimpleXMLElement $params */
    protected $params;
    /* @var array $parsed_params */
    protected $parsed_params;
    public function __construct(\SimpleXMLElement $request = null)
    {
        if (!$request) {
            $request = simplexml_load_string(file_get_contents("php://input"));
        }
        $this->request = $request;
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function getFunction()
    {
        $this->loadFunction();
        return $this->function->getName();
    }
    public function getParams()
    {
        if (!isset($this->parsed_params)) {
            $this->loadParams();
            $this->parsed_params = self::parseParams($this->params);
        }
        return $this->parsed_params;
    }
    public static function parseParams(\SimpleXMLElement $params)
    {
        $return_array = array();
        foreach ($params as $param) {
            if ($param->children()->count() > 1) {
                // Recursively parse the parameters for arrays/objects.
                $result = self::parseParams($param);
            } else {
                $result = (string)$param;
            }
            $return_array[ $param->getName() ] = $result;
        }
        return $return_array;
    }
    private function loadFunction()
    {
        if (!isset($this->function)) {
            $this->function = $this->request->children('env', true)->Body->children('ns1', true);
        }
    }
    private function loadParams()
    {
        $this->loadFunction();
        if (!isset($this->params)) {
            $this->params = $this->function->children();
        }
    }
}