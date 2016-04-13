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
                <form id="tag_form" role="form">
                    <input type="hidden" name="contacts_list" id="contacts_list"/>
                    <div class="form-group col-md-4">
                        <div class="col-md-8 m-b-10" style="padding: 0">
                            <!-- Using data-init-plugin='select2' automatically initializes a basic Select2 -->
                            <select class="full-width tag-name" name="name" id="tag_search_select">
                                <option></option>
                            </select>
                            <div class="tag-add" style="display: none;">
                                <div class="input-group m-b-5">
                                    <label class="input-group-addon info" for="tag_name"><i
                                            class="pg pg-plus_circle"></i></label>
                                    <input type="text" class="form-control tag-name m-b-5" name="name" id="tag_name" disabled="disabled">
                                    <input type="hidden" name="id" id="tag_id" disabled="disabled"/>
                                </div>
                                <div class="input-group">
                                    <label class="input-group-addon info" for="tag_start_date"><i
                                            class="pg pg-calender"></i></label>
                                    <input type="text" class="form-control datepicker"
                                           placeholder="Дата" name="start_date"
                                           id="tag_start_date" style="width: 60%" disabled="disabled">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 m-b-10"><button class="btn btn-info" id="tag_toggle">Создать тег</button></div>
                        <div class="col-md-8" style="padding: 0">
                            <select class="full-width" name="tag_users" id="tag_users_select">
                            </select>
                        </div>
                        <div class="col-md-6 checkbox check-success text-left p-l-5">
                            <input type="checkbox" name="as_task" class="" id="tag_as_task">
                            <label for="tag_as_task">Обозначить как Обзвон</label>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group col-md-8">
                        <div class="col-md-6">
                            <label class="" for="tag_description">Описание:</label>
                            <textarea name="description" id="tag_description" rows="6" cols="10"
                                  placeholder="Описание тега"
                                  class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="" for="tag_script">Скрипт:</label>
                            <textarea name="script" id="tag_script" rows="6" cols="10"
                                  placeholder="Скрипт тега"
                                  class="form-control"></textarea>
                        </div>
                        <div class="col-md-6 m-t-10">
                            <label class="">Добавить контакты:</label>
                            <div class="input-group">
                                <button class="btn btn-info" id="add_contact_table">Таблица контактов</button>
                            </div>
                            <br />
                            <div class="input-group">
                                <button class="btn btn-info" id="add_contact_csv">Импорт из CSV</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2 pull-right">
                        <button class="btn btn-complete pull-right disabled" id="tag_submit">Применить</button>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
            <div class="panel-body">
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
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END PANEL -->
    </div>
</div>

<!-- Modal -->
<div class="modal fade slide-up disable-scroll" id="modalAddContactToTag" tabindex="-1" role="dialog" aria-labelledby="modalSlideUpLabel" aria-hidden="false">
    <div class="modal-dialog ">
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
                                <?php if ($col_key == 'Связать'): ?>
                                    <th></th>
                                <?php else: ?>
                                    <th><?php echo($col_val['label']); ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <?php foreach ($filter_contact_cols as $col_key => $col_val): ?>
                                <? if (!in_array($col_key, $hide_contact_columns)): ?>
                                    <?php if ($col_val['have_search']): ?>
                                        <td><input type="text" data-column="<?php echo($col_key); ?>" class="form-control search-input-text"></td>
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

<script type="text/javascript">
    var hide_columns = <?= json_encode($hide_contact_columns); ?>;
    var columns = <?= json_encode(array_keys($table_contact_cols)); ?>;
    var columns_full = <?= json_encode($table_contact_cols); ?>;
</script>
