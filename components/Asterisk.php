<?php
namespace app\components;

class Asterisk
{
    protected $config;

    protected $configFilesToParse = [
        'sip',
    ];

    public function getExternalSips()
    {
        $sips = $this->getConfig('sip');
        $externalSips = [];
        foreach($sips as $name=>$config) {
            if(($config['context'] ?? '') === 'in') {
                $externalSips[] = $name;
            }
        }
        return $externalSips;
    }

    protected function getConfig($key = null)
    {
        if(!isset($this->config)) {
            foreach($this->configFilesToParse as $name) {
                $filePath = implode(DIRECTORY_SEPARATOR, [
                    \Yii::getAlias('@asterisk_config_folder'),
                    $name.'.conf',
                ]);
                if(is_file($filePath) && is_readable($filePath)) {
                    $this->config[$name] = $this->parseFile($filePath);
                }
            }
        }

        return is_null($key)?$this->config:($this->config[$key] ?? []);
    }

    protected function parseFile(string $path): array
    {
        $config = [];

        $file = fopen($path, 'r');
        if($file) {
            $currentSection = '__default__';
            $extendData = [];
            while(false != ($line = fgets($file))) {
                if(false !== strpos(trim($line), ';')) {
                    continue;
                }

                $matches = [];
                if(preg_match('/\[(.+)\](.*\((.+)\))?/', $line, $matches)) {
                    $currentSection = $matches[1];
                    $extendData = $config[$matches[3] ?? ''] ?? [];
                } elseif(false !== strpos($line, '=')) {
                    list($name, $value) = array_map('trim', explode('=', $line));

                    if(!isset($config[$currentSection])) {
                        $config[$currentSection] = $extendData;
                    }
                    $config[$currentSection][$name] = $value;
                }

            }
            fclose($file);
        }

        return $config;
    }
}