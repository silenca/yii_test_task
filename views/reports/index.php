<?php
/* @var $this yii\web\View */

use app\assets\TableAsset;
use app\assets\ReportsAsset;
use app\assets\DatarangepickerAsset;

TableAsset::register($this);
ReportsAsset::register($this);
DatarangepickerAsset::register($this);
$this->title = 'Отчеты';
$this->params['header_text'] = "Отчеты";
$this->params['active'] = 'reports';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/reports" class="active">Отчеты</a></li>
        </ul>
        <div class="panel panel-transparent">
            <div class="panel-body">
                <div class="col-lg-offset-9 col-md-3">
                    <div class="input-group">
                        <label class="input-group-addon" for="daterange">За период</label>
                        <input id="daterange" type="text" name="daterange" placeholder="За период" class="form-control"/>
                    </div>
                </div>
                <table class="table table-hover" id="reports-table">
                    <thead>
                        <tr>
                            <td>
                                <?php if (isset($managers)): ?>
                                    <select data-column="0"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                        <option value="0">Все операторы</option>
                                        <?php foreach ($managers as $manager): ?>
                                            <option value="<?php echo $manager->id ?>"><?php echo $manager->firstname ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    Менеджер
                                <?php endif; ?>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th>Менеджер</th>
                            <th>Исходящие звонки</th>
                            <th>Входящие звонки</th>
                            <th>Контакты</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="report-visual">
                <div class="bg-primary b-a b-grey incoming">
                    <div class="bg-white m-t-45 padding-10 text-master">
                        <p class="font-montserrat all-caps small m-b-5"></p>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="bg-complete b-a b-grey leads">
                    <div class="bg-white m-t-45 padding-10 text-master">
                        <p class="font-montserrat all-caps small m-b-5">@color-complete</p>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="bg-success b-a b-grey visit">
                    <div class="bg-white m-t-45 padding-10 text-master">
                        <p class="font-montserrat all-caps small m-b-5"></p>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="bg-success b-a b-grey show">
                    <div class="bg-white m-t-45 padding-10 text-master">
                        <p class="font-montserrat all-caps small m-b-5"></p>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="bg-warning b-a b-grey deal">
                    <div class="bg-white m-t-45 padding-10 text-master">
                        <p class="font-montserrat all-caps small m-b-5"></p>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
