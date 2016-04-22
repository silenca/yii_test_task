<?php
use app\assets\TableAsset;
use app\assets\ActionAsset;
use app\assets\DatapickerAsset;

TableAsset::register($this);
ActionAsset::register($this);
DatapickerAsset::register($this);

$this->title = "Действия";
$this->params['header_text'] = "Действия";
$this->params['active'] = 'action';
?>
<div class="content">
    <div class="container-fluid container-fixed-lg bg-white">
        <ul class="breadcrumb">
            <li><a href="/">Главная</a></li>
            <li><a href="/action" class="active">Действия</a></li>
        </ul>
        <!-- START PANEL -->
        <div class="panel panel-transparent">
            <div class="panel-body">
                <table class="table table-hover" id="action-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>
                                <select data-column="2"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Тип действия</option>
                                    <?php foreach ($action_types as $action_type): ?>
                                        <option value="<?php echo $action_type->id ?>"><?php echo $action_type->label ?></option>
                                    <?php endforeach; ?>
                                </select>

                            </th>
                            <th>Контакт</th>
                            <th>Запланированное время</th>
                            <th>Комментарий</th>
                            <th>
                                <?php if(isset($managers)): ?>
                                <select data-column="6"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Менеджер</option>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?php echo $manager->id ?>"><?php echo $manager->firstname ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php else: ?>
                                Менеджер
                                <?php endif; ?>
                            </th>
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