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
                                <select data-column="1"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
                                    <option value="0">Тип действия</option>
                                    <option value="scheduled_call">Запланированный исходящий звонок</option>
                                    <option value="email_now">Email сообщение</option>
                                    <option value="scheduled_email">Запланированное Email сообщение</option>
                                    <option value="incoming">Исходящий звонок</option>
                                    <option value="outgoing">Входящий звонок</option>
                                </select>

                            </th>
                            <th>Контакт</th>
                            <th>Запланированное время</th>
                            <th>Комментарий</th>
                            <th>
                                <?php if(isset($managers)): ?>
                                <select data-column="0"  class="cs-select cs-skin-slide search-input-select" data-init-plugin="cs-select">
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