<?php
/* @var $this yii\web\View */

use app\assets\TableAsset;
use app\assets\ReceivableAsset;
use app\assets\DatarangepickerAsset;

TableAsset::register($this);
ReceivableAsset::register($this);
DatarangepickerAsset::register($this);
$this->title = 'Отчет о дебиторской задолженности';
$this->params['header_text'] = "Отчет о дебиторской задолженности";
$this->params['active'] = 'receivable';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/receivable" class="active">дебиторские задолженности</a></li>
        </ul>
        <div class="panel panel-transparent">
            <div class="panel-body">
                <div class="col-lg-offset-9 col-md-3">
                    <div class="input-group">
                        <label class="input-group-addon" for="daterange">За период</label>
                        <input id="daterange" type="text" name="daterange" placeholder="За период" class="form-control"/>
                    </div>
                </div>
                <table class="table table-hover" id="receivables-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th><input type="text" data-column="0"  class="form-control search-input-text"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <?php if (isset($managers)): ?>
                                    <select data-column="1"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                        <option value="0">Все операторы</option>
                                        <?php foreach ($managers as $manager): ?>
                                            <option value="<?php echo $manager->id ?>"><?php echo $manager->firstname ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    Менеджер
                                <?php endif; ?>
                            </th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th>Контакт</th>
                            <th>Объект</th>
                            <th>Стоимость</th>
                            <th>Оплачено</th>
                            <th>Разница</th>
                            <th>Менеджер</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
