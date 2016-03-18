<?php
use app\assets\TableAsset;
use app\assets\ManagerNotifyAsset;

TableAsset::register($this);
ManagerNotifyAsset::register($this);

$this->title = "Уведомления";
$this->params['header_text'] = "Уведомления";
$this->params['active'] = 'notification';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/managernotify" class="active">Уведомления</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-body">
                <table class="table table-hover" id="manager_notify-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="width: 115px">Дата/время</th>
                            <th style="width: 250px;">Тип</th>
                            <th>Контакт</th>
                            <th>Сообщение от jivosite</th>
                            <th>Комментарий</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END PANEL -->
    </div>
</div>