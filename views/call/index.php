<?php

use app\assets\TableAsset;
use app\assets\CallAsset;

TableAsset::register($this);
CallAsset::register($this);

$this->title = "Звонки";
$this->params['header_text'] = "Звонки";
$this->params['active'] = 'call';
?>
<div class="content">

    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/call" class="active">Звонки</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-body">
                <table class="table table-hover" id="call-table">
                    <thead>
                        <tr>
                            <?php foreach($columns as $column) { ?>
                                <th class="dt_column" data-name="<?php echo $column['name']; ?>" data-visible="<?php echo $column['visible'] ?? 'true'; ?>">
                                    <?php
                                    switch($column['type']) {
                                        case 'select':
                                            ?>
                                            <?php if(!empty($column['options'])) { ?>
                                                <select data-column="5" class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                                    <option value="<?php echo $column['default'] ?? ""; ?>"><?php echo $column['label']; ?></option>
                                                    <?php foreach($column['options'] as $value=>$label) { ?>
                                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                    <?php } ?>
                                                </select>
                                            <?php } else { ?>
                                                <?php echo $column['label']; ?>
                                            <?php } ?>
                                            <?php
                                            break;
                                        default:
                                            echo $column['label'];
                                            break;
                                    }
                                    ?>
                                </th>
                            <?php } ?>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- END PANEL -->
    </div>
</div>

<?php echo $this->render('/parts/contact_form'); ?>