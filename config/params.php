<?php
Yii::setAlias('@web_folder', realpath(__DIR__.'/../web'));

return [
    'adminEmail' => 'admin@example.com',
    'host_notify' => 'http://127.0.0.1:8001',
    'host_notify_incoming' => 'http://127.0.0.1:8002/incoming',
    'host_notify_contract' => 'http://127.0.0.1:8002/contract',
];
