<?php

use app\assets\TableAsset;
use app\assets\ContractAsset;
use app\assets\SulutionFormAsset;

TableAsset::register($this);
ContractAsset::register($this);
SulutionFormAsset::register($this);

$this->title = "Договоры";
$this->params['active'] = 'contracts';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/contract" class="active">Контракты</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-body">
                <table class="table table-hover" id="contract-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата/время</th>
                            <th>Контакт</th>
                            <th>Менеджер</th>
                            <th>Объект</th>
                            <th>Цена</th>
                            <th>Комментарий</th>
                            <th>Одобрение</th>
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
<div class="solution_form"></div>