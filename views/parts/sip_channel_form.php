<?php
/**
 * sip_channel_form.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */
?>
<div class="modal fade slide-right modal-md modal-sm modal-xs"
     id="modalAddSipChannel" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" id="sip-channel-id" value=""/>
    <div class="modal-dialog drop-shadow modal-md modal-sm modal-xs">
        <div class="modal-content-wrapper">
            <div class="list-view-wrapper modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-times fa-2x"></i>
                </button>
                <div class="container-xs-height full-height">
                    <div class="row-xs-height">
                        <div class="modal-body sip-channel-modal col-middle text-center">
                            <div class="row">
                                <div class="sip-channel-data col-md-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title sip-channel-title">Новый SIP канал</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="sip_channel_phone_number"><i
                                                            class="fa fa-fw fa-phone"></i></label>
                                                <input type="text" id="sip_channel_phone_number" name="phone_number"
                                                       placeholder="Номер телефона" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="sip_channel_host"><i
                                                            class="fa fa-fw fa-server"></i></label>
                                                <input type="text" id="sip_channel_host" name="host"
                                                       placeholder="Хост" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="sip_channel_port"><i
                                                            class="fa fa-fw fa-server"></i></label>
                                                <input type="text" id="sip_channel_port" name="port"
                                                       placeholder="Порт" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="sip_channel_login"><i
                                                            class="fa fa-fw fa-lock"></i></label>
                                                <input type="text" id="sip_channel_login" name="login"
                                                       placeholder="Логин" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="sip_channel_password"><i
                                                            class="fa fa-fw fa-lock"></i></label>
                                                <input type="text" id="sip_channel_password" name="password"
                                                       placeholder="Пароль" class="form-control">
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
    </div>
</div>
