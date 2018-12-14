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
                    <?php if (Yii::$app->user->can('listen_call')): ?>
                    <th>Прослушать</th>
                    <?php endif; ?>
                    </thead>
                    <tbody>

                    <?php
                    foreach($calls as $call){
                        ?>
                    <tr>
                        <?php
                            foreach ($call as $key=>$value) {
                                if($key === 'record'){
                                    ?>
                                    <td>
                                        <audio controls="" src="https://dopomogaplus.silencatech.com/var/spool/asterisk/monitor/<?=$value?>.mp3" type="audio/mpeg"></audio>
                                    </td>
                                    <?php
                                }else if($key === 'contact'){
                                    ?>
                                    <td>
                                    <?php
                                    if(gettype($value)==='object'){
                                        if($value['is_deleted']){
                                        ?>
                                        <a class="contact" data-contact_id="" data-phone="<?=$value['first_phone']?>" href="javascript:void(0)"><?=$value['first_phone']?></a>
                                        <?php
                                        }else{
                                            echo $value['first_phone'];
                                        }
                                    }else{
                                        echo $value;
                                    }
                                    ?>
                                    </td><?php
                                }else{
                                ?>
                                <td><?= $value ?></td>
                                <?php }
                            }
                        }
                    ?>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END PANEL -->
    </div>
</div>

<?php echo $this->render('/parts/contact_form'); ?>