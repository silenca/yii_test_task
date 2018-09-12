<?php
/**
 * attraction_channel_form.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */
?>
<div class="modal fade slide-right modal-md"
     id="modalAddAttractionChannel" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" id="attraction-channel-id" value=""/>
    <div class="modal-dialog drop-shadow modal-lg">
        <div class="modal-content-wrapper">
            <div class="list-view-wrapper modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-times fa-2x"></i>
                </button>
                <div class="container-xs-height full-height">
                    <div class="row-xs-height">
                        <div class="modal-body attraction-channel-modal col-middle text-center">

                        </div>
                        <div class="row">
                            <div class="attraction-channel-data col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title attraction-channel-title">Новый канал привлечения</div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="input-group">
                                        <label class="input-group-addon primary" for="attraction_channel_name"><i
                                                class="fa fa-fw fa-th-large"></i></label>
                                        <input type="text" id="attraction_channel_name" name="name"
                                               placeholder="Наименование" class="form-control">
                                    </div>
                                    <br>
                                    <div class="input-group">
                                        <label class="input-group-addon primary" for="attraction_channel_active"><i
                                                    class="fa fa-fw fa-check"></i></label>
                                        <span style="padding: 10px">Активен</span>
                                        <input type="checkbox" id="attraction_channel_active" class="js-switch form-control" name="active"/>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                        <label class="input-group-addon primary" for="attraction_channel_type"><i
                                                class="fa fa-fw fa-list-alt"></i></label>
                                        <select id="attraction_channel_type" name="type" class="form-control">
                                            <option class="select-placeholder" value="" disabled selected>Тип</option>
                                            <?php
                                            $types = \app\models\AttractionChannel::TYPE_LABELS;
                                            foreach ($types as $v => $type) {
                                                echo '<option value="'.$v.'">'.$type.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                        <label class="input-group-addon primary" for="attraction_channel_sip_channel_id"><i
                                                    class="fa fa-fw fa-phone"></i></label>
                                        <select id="attraction_channel_sip_channel_id" name="sip_channel_id"
                                                 class="form-control">
                                            <option class="select-placeholder" value="" disabled selected>SIP Канал</option>
                                            <?php
                                            $channels = \app\models\SipChannel::find()->select('id,phone_number')->asArray()->all();
                                            foreach ($channels as $channel) {
                                                echo '<option value="'.$channel['id'].'">'.$channel['phone_number'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                        <label class="input-group-addon primary" for="attraction_channel_integration_type"><i
                                                    class="fa fa-fw fa-list-alt"></i></label>
                                        <select id="attraction_channel_integration_type" name="type" class="form-control">
                                            <option class="select-placeholder" value="" disabled selected>Интеграция</option>
                                            <?php
                                            $types = \app\models\AttractionChannel::INTEGRATIONS;
                                            foreach ($types as $type) {
                                                echo '<option value="'.$type.'">'.$type.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
