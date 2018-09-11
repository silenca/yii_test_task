<?php
/**
 * index.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

use app\assets\TableAsset;

TableAsset::register($this);
\app\assets\SipChannelAsset::register($this);


$this->title = "SIP Каналы";
$this->params['active'] = 'sip-channel';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/sip-channel" class="active"><?=\yii\helpers\Html::encode($this->title)?></a></li>
        </ul>
        <div class="panel panel-transparent">
            <div class="panel-heading">
                <div class="pull-left">
                    <div class="col-xs-12">
                        <button class="btn pull-left" id="column_filter">
                            <i class="pg-settings"></i>
                        </button>
                    </div>
                </div>
                <div class="pull-right">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-cons pull-right" id="open-new-sip-channel-from">
                            <i class="fa fa-plus"></i>
                            Добавить канал
                        </button>
                        <button class="btn btn-primary btn-cons pull-right" id="delete_all_filtered_contacts"
                                style="display: none;">
                            <i class="fa fa-trash"></i>
                            Удалить найденные контакты (<span>0</span> шт.)
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-hover" id="sip-channel-table">
                        <thead>
                        <tr>
                            <?php foreach ($table_cols as $col_key => $col_val): ?>
                                <th><?php echo($col_val['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->render('/parts/sip_channel_form'); ?>
<script type="text/javascript">
    var hide_columns = <?= json_encode([]); ?>;
    var columns = <?= json_encode(array_keys($table_cols)); ?>;
    var columns_full = <?= json_encode($table_cols); ?>;
</script>
