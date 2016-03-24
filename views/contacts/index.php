<?php

use app\assets\TableAsset;
use app\assets\ContactAsset;
use app\assets\SulutionFormAsset;


TableAsset::register($this);
ContactAsset::register($this);
if (Yii::$app->user->can('contracts')) {
    SulutionFormAsset::register($this);
}

$this->title = "Контакты";
$this->params['active'] = 'contact';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/contacts" class="active">Контакты</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-heading">
                <div class="pull-left">
                    <div class="col-xs-12">
                        <button class="btn pull-left" id="column_filter">
                            <i class="pg-settings"></i>
                        </button>
                    </div>
                    <div id="column_filter_modal" class="column_filter_modal" style="display: none">
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('int_id', $hide_columns) ? 'checked' : null ?>
                                   value="int_id" id="column_filter_int_id">
                            <label for="column_filter_int_id">№</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('surname', $hide_columns) ? 'checked' : null ?>
                                   value="surname" id="column_filter_surname">
                            <label for="column_filter_surname">Фамиилия</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('name', $hide_columns) ? 'checked' : null ?>
                                   value="name" id="column_filter_name">
                            <label for="column_filter_name">Имя</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('middle_name', $hide_columns) ? 'checked' : null ?>
                                   value="middle_name" id="column_filter_middle_name">
                            <label for="column_filter_middle_name">Отчество</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('phones', $hide_columns) ? 'checked' : null ?>
                                   value="phones" id="column_filter_Телефоны">
                            <label for="column_filter_Телефоны">Телефоны</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('emails', $hide_columns) ? 'checked' : null ?>
                                   value="emails" id="column_filter_emails">
                            <label for="column_filter_emails">Email</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('tags', $hide_columns) ? 'checked' : null ?>
                                   value="tags" id="column_filter_tags">
                            <label for="column_filter_tags">Теги</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('country', $hide_columns) ? 'checked' : null ?>
                                   value="country" id="column_filter_country">
                            <label for="column_filter_country">Страна</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('region', $hide_columns) ? 'checked' : null ?>
                                   value="region" id="column_filter_region">
                            <label for="column_filter_region">Регион</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('area', $hide_columns) ? 'checked' : null ?>
                                   value="area" id="column_filter_area">
                            <label for="column_filter_area">Область</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('city', $hide_columns) ? 'checked' : null ?>
                                   value="city" id="column_filter_city">
                            <label for="column_filter_city">Город</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('street', $hide_columns) ? 'checked' : null ?>
                                   value="street" id="column_filter_street">
                            <label for="column_filter_street">Улица</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('house', $hide_columns) ? 'checked' : null ?>
                                   value="house" id="column_filter_house">
                            <label for="column_filter_house">Дом</label>
                        </div>
                        <div class="checkbox check-success">
                            <input type="checkbox" <?= !in_array('flat', $hide_columns) ? 'checked' : null ?>
                                   value="flat" id="column_filter_flat">
                            <label for="column_filter_flat">Квартира</label>
                        </div>

                        <div class="col-xs-12">
                            <button class="btn btn-success btn-cons" id="column_filter_apply"><i
                                    class="fa fa-check"></i> Применить
                            </button>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-cons pull-right" id="open-new-contact-from"><i
                                class="fa fa-plus"></i> Добавить контакт
                        </button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <table class="table table-hover" id="contacts-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>№</th>
                        <th>Фамилия</th>
                        <th>Имя</th>
                        <th>Отчество</th>
                        <th>Телефоны</th>
                        <th>Email</th>
                        <th>Теги</th>
                        <th>Страна</th>
                        <th>Регион</th>
                        <th>Область</th>
                        <th>Город</th>
                        <th>Улица</th>
                        <th>Дом</th>
                        <th>Кваритра</th>
                        <th>Удалить</th>
                    </tr>
                    </thead>
                    <thead>
                    <tr>
                        <? if (!in_array('int_id', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('surname', $hide_columns)): ?>
                            <td><input type="text" data-column="3" class="form-control search-input-text"></td>
                        <? endif ?>
                        <? if (!in_array('name', $hide_columns)): ?>
                            <td><input type="text" data-column="4" class="form-control search-input-text"></td>
                        <? endif ?>
                        <? if (!in_array('middle_name', $hide_columns)): ?>
                            <td><input type="text" data-column="5" class="form-control search-input-text"></td>
                        <? endif ?>
                        <? if (!in_array('phones', $hide_columns)): ?>
                            <td><input type="text" data-column="6" class="form-control search-input-text"></td>
                        <? endif ?>
                        <? if (!in_array('emails', $hide_columns)): ?>
                            <td><input type="text" data-column="7" class="form-control search-input-text"></td>
                        <? endif ?>
                        <? if (!in_array('tags', $hide_columns)): ?>
                            <td><input type="text" data-column="8" class="form-control search-input-text"></td>
                        <? endif ?>
                        <td></td>
                        <? if (!in_array('country', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('region', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('area', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('city', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('street', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('house', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
                        <? if (!in_array('flat', $hide_columns)): ?>
                            <td></td>
                        <? endif ?>
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

<?php echo $this->render('/parts/contact_form'); ?>

<script type="text/javascript">
    var hide_columns = <?= json_encode($hide_columns); ?>
</script>
