<?php

use app\assets\ContactAsset;
use app\assets\GoogleApiAsset;
use app\assets\TableAsset;


TableAsset::register($this);
ContactAsset::register($this);
GoogleApiAsset::register($this);
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
                        <?php foreach ($filter_cols as $col_key => $col_val): ?>
                            <div class="checkbox check-success">
                                <input type="checkbox" <?= !in_array($col_key, $hide_columns) ? 'checked' : NULL ?>
                                       value="<?php echo($col_key); ?>" id="column_filter_<?php echo($col_key); ?>">
                                <label
                                    for="column_filter_<?php echo($col_key); ?>"><?php echo($col_val['label']); ?></label>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-xs-12">
                            <button class="btn btn-success btn-cons" id="column_filter_apply">
                                <i class="fa fa-check"></i>
                                Применить
                            </button>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-cons pull-right" id="open-new-contact-from">
                            <i class="fa fa-plus"></i>
                            Добавить контакт
                        </button>
                        <button class="btn btn-primary btn-cons pull-right" id="delete_all_filtered_contacts"
                                style="display: none;">
                            <i class="fa fa-trash"></i>
                            Удалить найденные контакты (<span>0</span> шт.)
                        </button>
                    </div>
                    <!--                    <div class="col-xs-6">-->
                    <!--                        <button class="btn btn-primary btn-cons pull-right" id="add_tag_to_all"><i-->
                    <!--                                class="fa fa-plus"></i> Добавить тег всем-->
                    <!--                        </button>-->
                    <!--                    </div>-->
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <table class="table table-hover" id="contacts-table">
                    <thead>
                    <tr>
                        <?php foreach ($table_cols as $col_key => $col_val): ?>
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
                        <?php foreach ($filter_cols as $col_key => $col_val): ?>
                            <?php if (!in_array($col_key, $hide_columns)): ?>
                                <?php if ($col_val['have_search']): ?>
                                    <?php if ($col_key == 'attraction_channel_id'):?>
                                        <td><select data-column="<?php echo($col_key); ?>" class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                                <?php if(!isset($col_val['value'])):?>
                                                <option class="select-placeholder" value="" selected>Канал привлечения</option>
                                                <?php else:?>
                                                    <option class="select-placeholder" value="">Канал привлечения</option>
                                                <?php endif;?>
                                                <?php
                                                $channels = \app\models\AttractionChannel::find()->all();
                                                foreach ($channels as  $channel) {
                                                    if(isset($col_val['value']) && ($col_val['value'] == $channel->id))
                                                        echo '<option value="'.$channel->id.'" selected>'.$channel->name.'</option>';
                                                    else
                                                        echo '<option value="'.$channel->id.'">'.$channel->name.'</option>';

                                                }
                                                ?></select></td>
                                    <?php else: ?>
                                        <td><input type="text" data-column="<?php echo($col_key); ?>" class="form-control search-input-text" <?=(isset($col_val['value']))?'value="'.$col_val['value'].'"':""?>></td>
                                    <?php endif;?>
                                    <?php if ($col_key == 'status'):?>
                                        <td><select data-column="<?php echo($col_key); ?>" class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                                <?php if(!isset($col_val['value'])):?>
                                                <option class="select-placeholder" value="" selected>Статус</option>
                                                <?php else:?>
                                                    <option class="select-placeholder" value="">Статус</option>
                                                <?php endif;?>
                                                <?php
                                                $statuses = \app\models\Contact::$statuses;
                                                foreach ($statuses as  $key=>$value) {

                                                        echo '<option value="'.$key.'">'.$value.'</option>';

                                                }
                                                ?></select></td>
                                    <?php else: ?>
                                        <td><input type="text" data-column="<?php echo($col_key); ?>" class="form-control search-input-text" <?=(isset($col_val['value']))?'value="'.$col_val['value'].'"':""?>></td>
                                    <?php endif;?>
                                <?php else: ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
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

<div class="dropdown-menu link_with-dropdown pull-left" role="menu">
    <form class="link_with-form form-inline">
        <div class="form-group">
            <div class="input-group">
                <input type="text" name="search" class="search form-control">
            </div>
        </div>
        <button class="btn btn-sm btn-complete inline link_btn m-l-10">Связать</button>
        <img
            class="loader m-l-30"
            src="media/img/progress/progress-circle-primary.svg" alt="Progress"
            style="display: none; width: 30px; height: 30px">
        <!--        <ul class="result list-group m-t-5"></ul>-->
        <table class="result table table-hover m-t-5">
            <tbody>
            </tbody>
        </table>
    </form>
</div>

<?php //echo $this->render('/parts/contact_form'); ?>

<script type="text/javascript">
    var hide_columns = <?= json_encode($hide_columns); ?>;
    var columns = <?= json_encode(array_keys($table_cols)); ?>;
    var columns_full = <?= json_encode($table_cols); ?>;
</script>


