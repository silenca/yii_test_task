<?php

use app\assets\TableAsset;
use app\assets\TagAsset;

TableAsset::register($this);
TagAsset::register($this);

$this->title = "Теги";
$this->params['active'] = 'tags';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/tags" class="active">Теги</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-heading">
                <div class="row well">
                    <form id="tag_form" role="form">
                        <div class="form-group col-md-5">
                            <div class="col-md-8 m-b-10" style="padding: 0">
                                <!-- Using data-init-plugin='select2' automatically initializes a basic Select2 -->
                                <select class="full-width tag-name" name="name" id="tag_search_select">
                                    <option></option>
                                </select>
                                <div id="tag-add-field" style="display: none;">
                                    <div class="input-group m-b-5">
                                        <label class="input-group-addon info" for="tag_name"><i
                                                class="pg pg-plus_circle"></i></label>
                                        <input type="text" class="form-control tag-name m-b-5" name="name" id="tag_name"
                                               disabled="disabled">
                                        <input type="hidden" name="id" id="tag_id" disabled="disabled"/>
                                    </div>
                                    <div class="input-group">
                                        <label class="input-group-addon info" for="tag_start_date"
                                               style="display: none"><i
                                                class="pg pg-calender"></i></label>
                                        <input type="text" class="form-control datepicker"
                                               placeholder="Дата" name="start_date"
                                               id="tag_start_date" style="width: 60%; display: none"
                                               disabled="disabled">
                                    </div>
                                </div>
                            </div>
                            <?php if (Yii::$app->user->can('edit_tag')): ?>
                                <div class="col-md-4 m-b-10" style="padding-right: 0">
                                    <div id="tag-controls" class="" style="display: none;">
                                        <a class="ok" href="#"><i class="fa-2x fa fa-check"></i></a>
                                        <a class="remove" href="#"><i class="fa-2x fa fa-remove"></i></a>
                                    </div>
                                    <div class="">
                                        <button class="btn btn-info" id="tag_create" data-toggle='tooltip'
                                                title="Создать новый тэг">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <button class="btn btn-info" id="tag_delete" style="display: none;"
                                                data-toggle='tooltip' title="Удалить тэг">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <button class="btn btn-info" id="tag_restore" style="display: none;"
                                                data-toggle='tooltip' title="Восстановить тэг">
                                            <i class="fa fa-arrow-up"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-8" style="padding: 0">
                                    <select class="full-width" name="tag_users" id="tag_users_select" disabled="disabled">
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 checkbox check-success text-left p-l-5">
                                    <input type="checkbox" name="as_task" class="" id="tag_as_task" disabled="disabled">
                                    <label for="tag_as_task">Обозначить как Обзвон</label>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="form-group col-md-7">
                            <div class="col-md-6">
                                <label class="" for="tag_description">Описание:</label>
                                <textarea readonly name="description" id="tag_description" rows="6" cols="10"
                                          placeholder="Описание тега"
                                          class="form-control"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="" for="tag_script">Скрипт:</label>
                                <textarea readonly name="script" id="tag_script" rows="6" cols="10"
                                          placeholder="Скрипт тега"
                                          class="form-control"></textarea>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </form>
                    <?php if (Yii::$app->user->can('edit_tag')): ?>
                        <div class="col-md-7 col-md-offset-5">
                            <div class="col-md-6 m-t-10 add-contacts">
                                <label class="">Добавить контакты:</label>
                                <div class="input-group">
                                    <button class="btn btn-info disabled" id="add_contact_table">Таблица контактов
                                    </button>
                                </div>
                                <br/>
                                <div class="input-group">
                                    <button class="btn btn-info disabled" id="add_contact_csv">Импорт из CSV</button>
                                </div>
                            </div>
                            <div class="col-md-6 m-t-35 export-contacts">
                                <div class="input-group">
                                    <form action="/tags/export-csv" method="POST" id="exportCsv">
                                        <input type="hidden" name="contact_ids" id="contacts_list"/>
                                        <input type="hidden" name="tag_id"/>
                                        <input type="hidden" name="manager_id"/>
                                        <input type="hidden" name="status"/>
                                        <input type="hidden" name="comment"/>
                                        <input type="hidden" name="attitude_level"/>
                                        <input type="hidden" name="_csrf"
                                               value="<?= Yii::$app->request->getCsrfToken() ?>"/>
                                        <input type="submit" id="export_csv" class="btn btn-info disabled"
                                               value="Экспортировать в CSV файл"/>
                                    </form>
                                    <!--                                <button class="btn btn-info disabled" id="export_csv" data-href="/tags/export-csv">-->
                                    <!--                                    Экспортировать в CSV файл-->
                                    <!--                                </button>-->
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel-body">
                <div id="ring_counter" class="m-b-10 m-l-10">
                    <span>0</span> из <span>0</span> Контактов<br>
                    Осталось обзвонить: <span>0</span>
                </div>
                <table class="table table-hover" id="tag_contacts_table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>№</th>
                        <th>Контакт</th>
                        <th>Телефоны</th>
                        <th>Оператор</th>
                        <th>Статус</th>
                        <th>Комментарий</th>
                        <th>Реакция</th>
                    </tr>
                    </thead>
                    <thead>
                    <tr>
                        <td></td>
                        <td></td>
                        <?php if (Yii::$app->user->identity->getUserRole() !== 'operator'): ?>
                            <td></td>
                        <?php endif; ?>
                        <td>
                            <?php if (!empty($managers)): ?>
                                <select data-column="4" data-column_name="manager_id"
                                        class="cs-select cs-skin-slide search-input-select"
                                        data-init-plugin="cs-select">
                                    <option value="0">Все</option>
                                    <?php foreach ($managers as $manager): ?>
                                        <option
                                            value="<?php echo $manager->id ?>"><?php echo $manager->firstname ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($call_statuses)): ?>
                                <select data-column="5" data-column_name="status"
                                        class="cs-select cs-skin-slide search-input-select"
                                        data-init-plugin="cs-select">
                                    <option value="0">Все</option>
                                    <?php foreach ($call_statuses as $call_status): ?>
                                        <option
                                            value="<?php echo $call_status['name'] ?>"><?php echo $call_status['label'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td><input type="text" data-column="6" data-column_name="comment"
                                   class="form-control search-input-text"></td>
                        <td>
                            <?php if (!empty($attitude_levels)): ?>
                                <select data-column="7" data-column_name="attitude_level"
                                        class="cs-select cs-skin-slide search-input-select"
                                        data-init-plugin="cs-select">
                                    <option value="0">Все</option>
                                    <?php foreach ($attitude_levels as $attitude_level): ?>
                                        <option
                                            value="<?php echo $attitude_level['name'] ?>"><?php echo $attitude_level['label'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <input type="button" id="update_contacts_table" class="btn btn-info disabled"
                       value="Обновить список контактов"/>
            </div>
        </div>
        <!-- END PANEL -->
    </div>
</div>

<!-- Modal -->
<div class="modal fade slide-up disable-scroll in" id="modalAddContactToTag" tabindex="-1" role="dialog"
     aria-labelledby="modalSlideUpLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content-wrapper">
            <div class="modal-content">
                <div class="modal-header clearfix text-left">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="pg-close fs-14"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover" id="contacts-table">
                        <thead>
                        <tr>
                            <?php foreach ($table_contact_cols as $col_key => $col_val): ?>
                                <th><?php echo($col_val['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <?php foreach ($filter_contact_cols as $col_key => $col_val): ?>
                                <? if (!in_array($col_key, $hide_contact_columns)): ?>
                                    <?php if ($col_val['have_search']): ?>
                                        <td><input type="text" data-column="<?php echo($col_key); ?>"
                                                   class="form-control search-input-text"></td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif; ?>
                                <? endif ?>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button class="btn btn-info" id="add_contact">Добавить</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!-- /.modal-dialog -->

<!-- Modal: Import from CSV -->
<div class="modal fade slide-up disable-scroll" id="modalImportCsv" tabindex="-1" role="dialog"
     aria-labelledby="modalSlideUpLabel" aria-hidden="false">
    <div class="modal-dialog ">
        <div class="modal-content-wrapper">
            <div class="modal-content">
                <div class="modal-header clearfix text-left">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="pg-close fs-14"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="panel panel-transparent">
                        <div class="panel-heading">
                            <button class="btn btn-primary btn-cons" id="choose-file"><i class="fa fa-plus"></i> Выбрать
                                файл
                            </button>

                            <div id="file-name"></div>
                        </div>
                        <div class="panel-body">
                            <form action="import/csv" id="import_csv_form" method="POST"
                                  enctype="application/x-www-form-urlencoded">
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
        <!-- /.modal-content -->
    </div>
</div>
<!-- /.modal-dialog -->

<?php echo $this->render('/parts/contact_form'); ?>

<script type="text/javascript">
    var hide_columns = <?= json_encode($hide_contact_columns); ?>;
    var columns = <?= json_encode(array_keys($table_contact_cols)); ?>;
    var columns_full = <?= json_encode($table_contact_cols); ?>;
    var contact_ids = <?= (!empty(Yii::$app->request->post('contact_ids')) ? '"' . Yii::$app->request->post('contact_ids') . '"' : '""'); ?>
</script>
