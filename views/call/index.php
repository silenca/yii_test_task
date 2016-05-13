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
                            <th>ID</th>
                            <th></th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>
                                <select data-column="4" class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Тип</option>
                                    <?php foreach ($call_statuses as $call_status): ?>
                                        <option value="<?php echo $call_status['name'] ?>"><?php echo $call_status['label'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </th>
                            <th>
                                <?php if (isset($managers)): ?>
                                    <select data-column="5" class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                        <option value="0">Менеджер</option>
                                        <?php foreach ($managers as $manager): ?>
                                            <option value="<?php echo $manager->id ?>"><?php echo $manager->firstname ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    Менеджер
                                <?php endif; ?>
                            </th>
                            <th>Контакт</th>
                            <th>Теги</th>
                            <?php if (Yii::$app->user->can('listen_call')): ?>
                                <th>Прослушать</th>
                            <?php endif; ?>
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