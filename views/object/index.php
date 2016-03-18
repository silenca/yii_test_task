<?php

use app\assets\TableAsset;
use app\assets\ObjectAsset;

TableAsset::register($this);
ObjectAsset::register($this);
$this->title = "Объекты";
$this->params['header_text'] = "Объекты";
$this->params['active'] = 'object';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/object" class="active">Объекты</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-body">
                <table class="table table-hover" id="object-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ссылка</th>
                            <th>Очередь</th>
                            <th>Корпус</th>
                            <th>Этаж</th>
                            <th>Квартира</th>
                            <th>Площадь</th>
                            <th>Статус</th>
                            <th>Комнат</th>
                            <th>Комментарий</th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <td>
                                <select data-column="0"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Ничего не выбрано</option>
                                    <?php foreach ($queues as $queue): ?>
                                        <option value="<?php echo $queue->id ?>"><?php echo $queue->queue ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select data-column="1"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Ничего не выбрано</option>
                                    <?php foreach ($houses as $house): ?>
                                        <option value="<?php echo $house->id ?>"><?php echo $house->housing ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select data-column="2"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Ничего не выбрано</option>
                                    <?php foreach ($floors as $floor): ?>
                                        <option value="<?php echo $floor->id ?>"><?php echo $floor->floor ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" data-column="3"  class="search-input-text">
                            </td>
                            <td>
                                <input type="text" data-column="4"  class="search-input-text">
                            </td>
                            <td>
                                <select data-column="5"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Ничего не выбрано</option>
                                    <option value="active">Продано</option>
                                    <option value="inactive">На показе</option>
                                </select>
                            </td>
                            <td>
                                <select data-column="6"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Ничего не выбрано</option>
                                    <?php foreach ($layouts as $layout): ?>
                                        <option value="<?php echo $layout->layout ?>"><?php echo $layout->layout ?></option>
                                    <?php endforeach; ?>
                                </select></td>
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

<div class="modal fade stick-up" id="modalEditComment" tabindex="-1" role="dialog" aria-labelledby="modalEditCommentLabel" aria-hidden="true">
    <input type="hidden" id="object-id" value=""/>
    <div class="modal-dialog">
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
                            <label for="object_comment">Комментарий</label>
<!--                            <input type="text" name="comment" id="object_comment" class="form-control" >-->
                            <textarea name="comment" id="object_comment" cols="5" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 right-block">
                        <button type="button" class="btn btn-primary btn-sm btn-block add_comment-btn">
                            Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>