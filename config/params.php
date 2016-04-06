<?php
Yii::setAlias('@web_folder', realpath(__DIR__.'/../web'));

return [
    'adminEmail' => 'admin@example.com',
    'host_notify' => 'http://127.0.0.1:8005',
    'host_notify_incoming' => 'http://127.0.0.1:8002/incoming',
    'call_order_script' => '/var/www/call.openrussia.org/call_order.php'
];
