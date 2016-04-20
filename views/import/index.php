<?php

use app\assets\TableAsset;
use app\assets\ImportAsset;

TableAsset::register($this);
ImportAsset::register($this);

$this->title = "Импорт";
$this->params['active'] = 'import';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/import" class="active">Импорт</a></li>
        </ul>
        <div class="col-md-6">
            <div class="panel panel-transparent">
                <div class="panel-heading">
                    <button class="btn btn-primary btn-cons" id="choose-file"><i class="fa fa-plus"></i> Выбрать
                        файл
                    </button>

                    <div id="file-name"></div>
                </div>
                <div class="panel-body">
                    <form action="import/csv" id="import_csv_form" method="POST" enctype="application/x-www-form-urlencoded">
                        <input type="file" name="csv_file" id="csv-file" style="display: none" accept=".csv"/>
                        <input type="submit" class="btn btn-complete btn-cons" value="Начать импорт"/>
                        <div class="result">

                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
