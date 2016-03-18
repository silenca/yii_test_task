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
                <div class="pull-right">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-cons pull-right" id="open-new-tag-form"><i class="fa fa-plus"></i> Добавить тег</button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <table class="table table-hover" id="tags-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Редактировать</th>
                        <th>Удалить</th>
                    </tr>
                    </thead>
                    <thead>
                    <tr>
                        <td><input type="text" data-column="0"  class="form-control search-input-text"></td>
                        <td></td>
                        <td></td>
                        <td></td>
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

<div class="modal fade stick-up" id="modalTagForm" tabindex="-1" role="dialog" aria-labelledby="modalTagFormLabel" aria-hidden="true">
    <input type="hidden" name="id" class="form-input" id="tag_id" value=""/>
    <!-- .modal-dialog -->
    <div class="modal-dialog">
        <!-- .modal-content -->
        <div class="modal-content">
            <div class="modal-header clearfix text-left">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="pg-close fs-14"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="tag_name">Название</label>
                            <input type="text" name="name" id="tag_name" class="form-control form-input" >
                        </div>
                        <div class="form-group">
                            <label for="tag_description">Описание</label>
                            <textarea name="description" id="tag_description" cols="5" rows="3" class="form-control form-input"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 right-block">
                        <button type="button" class="btn btn-primary btn-sm btn-block add_tag-btn">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>