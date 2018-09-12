<?php
/**
 * index.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

use app\assets\TableAsset;

TableAsset::register($this);
\app\assets\AttractionChannelAsset::register($this);


$this->title = "Каналы привлечения";
$this->params['active'] = 'attraction-channel';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/attraction-channel" class="active"><?=\yii\helpers\Html::encode($this->title)?></a></li>
        </ul>
        <div class="panel panel-transparent">
            <div class="panel-heading">
                <div class="pull-left">
                    <div class="col-xs-12">
                        <button class="btn pull-left" id="column_filter">
                            <i class="pg-settings"></i>
                        </button>
                        <div id="column_filter_modal" class="column_filter_modal" style="display: none;z-index: 1000">
                            <?php foreach ($filter_cols as $col_key => $col_val): ?>
                                <div class="checkbox check-success">
                                    <input type="checkbox" <?= !in_array($col_key, $hide_columns) ? 'checked' : null ?>
                                           value="<?php echo($col_key); ?>" id="column_filter_<?php echo($col_key); ?>">
                                    <label for="column_filter_<?php echo($col_key); ?>"><?php echo($col_val['label']); ?></label>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-xs-12">
                                <button class="btn btn-success btn-cons" id="column_filter_apply"><i
                                            class="fa fa-check"></i> Применить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-cons pull-right" id="open-new-attraction-channel-from">
                            <i class="fa fa-plus"></i>
                            Добавить канал
                        </button>
<!--                        <button class="btn btn-primary btn-cons pull-right" id="delete_all_filtered_contacts"-->
<!--                                style="display: none;">-->
<!--                            <i class="fa fa-trash"></i>-->
<!--                            Удалить найденные контакты (<span>0</span> шт.)-->
<!--                        </button>-->
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-hover" id="attraction-channel-table">
                        <thead>
                        <tr>
                            <?php foreach ($table_cols as $col_key => $col_val): ?>
                                <th><?php echo($col_val['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <?php foreach ($filter_cols as $col_key => $col_val): ?>
                                <?php if (!in_array($col_key, $hide_columns)): ?>
                                    <?php if ($col_val['have_search']): ?>
                                        <td><input type="text" data-column="<?php echo($col_key); ?>" class="form-control search-input-text"></td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif; ?>
                                <?php endif ?>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->render('/parts/attraction_channel_form'); ?>
<script type="text/javascript">
    var hide_columns = <?= json_encode($hide_columns); ?>;
    var columns = <?= json_encode(array_keys($table_cols)); ?>;
    var columns_full = <?= json_encode($table_cols); ?>;
</script>
