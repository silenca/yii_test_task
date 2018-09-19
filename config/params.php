<?php
Yii::setAlias('@web_folder', realpath(__DIR__.'/../web/'));
Yii::setAlias('@runtime_log_folder', realpath(__DIR__.'/../runtime/logs'));

return [
    'adminEmail' => 'admin@example.com',
    'host_notify' => 'http://127.0.0.1:8005',
    'host_notify_incoming' => 'http://127.0.0.1:8002/incoming',
    'host_notify_close_call' => 'http://127.0.0.1:8002/close-call',
    'call_order_script' => '/var/www/call.openrussia.org/call_order.php',

    'crm_host' => 'http://127.0.0.1:8086',
    'call_crm_host' => 'http://sip3.openrussia.org/',
];
