<?php
use app\models\User;
?>
<div class="modal fade slide-right modal-lg modal-sm modal-xs col-3"
    id="modalAddContact" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" id="contact-id" value=""/>
    <div
        class="modal-dialog drop-shadow modal-lg modal-sm modal-xs col-3">
        <div class="modal-content-wrapper">
            <div class="list-view-wrapper modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-times fa-2x"></i>
                </button>
                <div class="container-xs-height full-height">
                    <div class="row-xs-height">
                        <div class="modal-body contact-modal col-middle text-center">
                            <div class="m-b-10 text-left contact-manager-name-cont">
                                <span class="label label-inverse">Ответственный - <span
                                        id="contact_manager_name"></span></span>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-12 contact-data">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title contact-title">Новый контакт</div>
                                            <div class="text-warning contact-deleted">Контакт удален</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_surname"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="contact_surname" name="surname"
                                                       placeholder="Фамилия контакта" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_name"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="contact_name" name="name"
                                                       placeholder="Имя контакта" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_middle_name"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="contact_middle_name" name="middle_name"
                                                       placeholder="Отчество контакта" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_phones"><i
                                                        class="fa fa-fw fa-phone"></i></label>
                                                <input type="text" id="contact_phones" name="phones"
                                                       placeholder="Номера телефонов" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_emails"><i
                                                        class="fa fa-fw fa-envelope"></i></label>
                                                <input type="text" id="contact_emails" name="emails" placeholder="Email"
                                                       class="form-control">
                                            </div>

                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_country"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_country" name="country"
                                                       placeholder="Страна проживания" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_region"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_region" name="region"
                                                       placeholder="Регион проживания" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_area"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_area" name="area" placeholder="Район"
                                                       class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_city"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_city" name="city"
                                                       placeholder="Город/поселок проживания" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_street"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_street" name="street" placeholder="Улица"
                                                       class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_house"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_house" name="house"
                                                       placeholder="Номер дома" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_flat"><i
                                                        class="pg pg-home"></i></label>
                                                <input type="text" id="contact_flat" name="flat"
                                                       placeholder="Номер квартиры" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <?php $disabled = (Yii::$app->user->can('attraction_channel')?false:true);?>
                                                <label class="input-group-addon primary" for="attraction_channel"><i
                                                            class="fa fa-fw fa-list-alt"></i></label>
                                                <select id="attraction_channel" name="attraction_channel_id" class="form-control"
                                                 <?=$disabled?'disabled':''?>>
                                                    <option class="select-placeholder" value="" disabled selected>Канал привлечения</option>
                                                    <?php
                                                    $channels = \app\models\AttractionChannel::find()->where(['is_active'=>1])->all();
                                                    foreach ($channels as  $channel) {
                                                        echo '<option value="'.$channel->id.'">'.$channel->name.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="status"><i
                                                            class="fa fa-fw fa-list-alt"></i></label>
                                                <select id="status" name="status" class="form-control"
                                                    <option class="select-placeholder" value=""  selected>Статус</option>
                                                    <?php
                                                    $statuses = \app\models\Contact::$statuses;
                                                    foreach ($statuses as  $status) {
                                                        echo '<option value="'.$status.'">'.$status.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <hr>
                                            <div class="form-group text-left">
                                                <input id="contact_tags" name="tags_str" class="contact-tags" type="text" />
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12"
                                    class="contact-history col-md-4">
                                    <div class="panel panel-transparent ">
                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs nav-tabs-fillup history-header">
                                            <li class="history-tab active">
                                                <a data-toggle="tab" href="#history"
                                                   aria-expanded="true"><span>История</span></a>
                                            </li>
                                            <li class="script-tab">
                                                <a data-toggle="tab" href="#script" aria-expanded="false"><span>Скрипт</span></a>
                                            </li>
                                        </ul>
                                        <!-- Tab panes -->
                                        <div class="tab-content">
                                            <div class="tab-pane slide-left active" id="history">
                                                <div class="row">
                                                    <div class="panel panel-default contact-history-rel">
                                                        <div class="panel-heading">
                                                            <div class="panel-title">История взаимоотношений</div>
                                                        </div>
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="scroll-window-content" style="height: 250px">
                                                                    <img
                                                                        class="history_loader image-responsive-height m-t-45 demo-mw-100"
                                                                        src="media/img/progress/progress.svg" alt="Progress"
                                                                        style="display: none">
                                                                    <div class="history_content">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <textarea id="contact-comment" rows="6" cols="10"
                                                                          placeholder="Комментарий"
                                                                          class="form-control"></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <button id="add-comment" class="btn btn-complete btn-block">
                                                                    Добавить
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane slide-left" id="script">
                                                <div class="row">
                                                    <div class="panel panel-default contact-script-rel">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="" style="height: 600px; overflow-y: auto">
                                                                    <img
                                                                        class="script_loader image-responsive-height m-t-45 demo-mw-100"
                                                                        src="media/img/progress/progress.svg" alt="Progress"
                                                                        style="display: none">
                                                                    <div class="script_content text-left">
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
                                <div class="contact-actions col-md-4 col-sm-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title block">
                                                <div class="form-group cs-select-container cs-select-block">
                                                    <select id="contact-action" class="cs-select cs-skin-slide"
                                                            data-init-plugin="cs-select">
                                                        <option value="0">Действия</option>
                                                        <option value="call">Звонок</option>
                                                        <option value="email">Email</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="contact-actions" class="panel-body">
                                            <div id="action_call" class="contact-action" style="display: none">
                                                <form id="form_action_call" action="/contacts/objectschedulecall"
                                                      method="POST">
                                                    <input type="hidden" name="call_order_token" class="call_order_token">
                                                    <div class="panel panel-default block form-group" style="display: none">
                                                        <div class="row">
                                                            <div class="checkbox check-success text-left p-l-10">
                                                                <input type="checkbox" name="action_send_now"
                                                                       class="action_send_now" id="action_send_now_phone">
                                                                <label for="action_send_now_phone">Отправить сейчас</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group action-title">
                                                        <label>Запланировать звонок</label>
                                                    </div>
                                                    <div class="form-group input-group datepicker-content">
                                                        <label class="input-group-addon success"
                                                               for=""><i
                                                                class="fa fa-fw fa-calendar"></i></label>
                                                        <input name="schedule_date" type="text"
                                                               class="form-control object-schedule-datetime datepicker"
                                                               placeholder="Когда применить?"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="checkbox check-success text-left">
                                                            <input type="checkbox" class="google-cal-show"
                                                                   id="google_cal_show_call">
                                                            <label for="google_cal_show_call">В Google Calendar</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group text-left" style="display: none">
                                                        <label for="action_tag_description">Описание:</label>
                                                        <textarea name="action_tag_description" id="action_tag_description" rows="10" cols="10"
                                                                      class="action-tag-description form-control"></textarea>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group">
                                                        <textarea name="action_comment" id="call_action-comment" rows="6" cols="10"
                                                                      placeholder="Комментарий"
                                                                      class="action-comment form-control"></textarea>
                                                    </div>
                                                    <div class="panel panel-default block form-group attitude padding-10" style="display: none">
                                                        <div class="row">
                                                            <div class="text-left m-b-10">Отношение человека к звонку:</div>
                                                            <div class="inline m-r-20">Негативное</div>
                                                            <div class="inline m-r-20">Нейтральное</div>
                                                            <div class="inline">Позитивное</div>
                                                            <div class="radio radio-primary">
                                                                    <input type="radio" name="attitude" class="call-attitude" value="1" id="action_call_attitude_1">
                                                                <label for="action_call_attitude_1"></label>
                                                                    <input type="radio" name="attitude" class="call-attitude" value="2" id="action_call_attitude_2">
                                                                <label for="action_call_attitude_2"></label>
                                                                    <input type="radio" name="attitude" class="call-attitude" value="3" id="action_call_attitude_3" checked="checked">
                                                                <label for="action_call_attitude_3"></label>
                                                                    <input type="radio" name="attitude" class="call-attitude" value="4" id="action_call_attitude_4">
                                                                <label for="action_call_attitude_4"></label>
                                                                    <input type="radio" name="attitude" class="call-attitude" value="5" id="action_call_attitude_5">
                                                                <label for="action_call_attitude_5"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <button class="btn btn-complete btn-block" type="submit">
                                                            Добавить
                                                        </button>
                                                    </div>

                                                </form>
                                            </div>
                                            <div id="action_email" class="contact-action" style="display: none">
                                                <form id="form_action_email" action="/contacts/objectscheduleemail"
                                                      method="POST">
                                                    <div class="panel panel-default block form-group">
                                                        <div class="row">
                                                            <div class="checkbox check-success text-left p-l-10">
                                                                <input type="checkbox" name="action_send_now" class="action_send_now" id="action_send_now_email">
                                                                <label for="action_send_now_email">Отправить сейчас</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group action-title">
                                                        <label>Запланировать Email</label>
                                                    </div>
                                                    <div class="form-group input-group datepicker-content">
                                                        <label class="input-group-addon success"
                                                               for=""><i
                                                                class="fa fa-fw fa-calendar"></i></label>
                                                        <input name="schedule_date" type="text"
                                                               class="form-control object-schedule-datetime datepicker"
                                                               placeholder="Когда применить?"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="checkbox check-success text-left">
                                                            <input type="checkbox" class="google-cal-show"
                                                                   id="google_cal_show_email">
                                                            <label for="google_cal_show_email">В Google Calendar</label>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group">
                                                            <textarea name="action_comment" id="email_action-comment" rows="6" cols="10"
                                                                      placeholder="Комментарий"
                                                                      class="action-comment form-control"></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <button class="btn btn-complete btn-block" type="submit">
                                                            Добавить
                                                        </button>
                                                    </div>
                                                </form>
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