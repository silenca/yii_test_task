<?php
use app\models\User;
?>

<link href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css" rel="stylesheet">
<!--<audio id="audio-remote" autoplay></audio>-->
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
                                                <label class="input-group-addon primary" for="contact_city"><i
                                                            class="pg pg-home"></i></label>
                                                <input type="text" id="contact_city" name="city"
                                                       placeholder="Город/поселок проживания" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group datepicker-content">
                                                <label class="input-group-addon primary" for="contact_birthday"><i
                                                            class="pg pg-home"></i></label>
                                                <input type="text" width="276"  id="contact_birthday" name="birthday"
                                                       placeholder="Дата рождения" class="form-control ">
                                            </div>

                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_attraction_channel_id"><i
                                                            class="fa fa-fw fa-list-alt"></i></label>
                                                <select id="contact_attraction_channel_id" name="attraction_channel_id" class="form-control select2-single">
                                                    <option class="select-placeholder" value="" disabled selected>Канал
                                                        привлечения
                                                    </option>
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
                                                <label class="input-group-addon primary" for="contact_manager_id"><i
                                                            class="fa fa-fw fa-list-alt"></i></label>
                                                <select id="contact_manager_id" name="manager_id" class="form-control select2-single">
                                                    <option class="select-placeholder" value="" disabled selected>Ответственный</option>
                                                    <?php
                                                    $users = User::find()->where(['role'=>5])->all();
                                                    foreach ($users as  $user) {
                                                        echo '<option value="'.$user->id.'">'.$user->firstname.' '.$user->lastname.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_status"><i
                                                            class="fa fa-fw fa-list-alt"></i></label>
                                                <select id="contact_status" name="status" class="form-control">
                                                    <option class="select-placeholder" value="" disabled selected>
                                                        Статус
                                                    </option>
                                                    <?php
                                                    $statuses = \app\models\Contact::$statuses;
                                                    foreach ($statuses as  $key=>$value) {
                                                        echo '<option value="'.$key.'">'.$value.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
                                            <hr>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_language_id"><i
                                                            class="fa fa-fw fa-language"></i></label>
                                                <select id="contact_language_id" name="language_id" class="form-control select2-single">
                                                    <?php
                                                    echo '<option class="select-placeholder" value="" disabled selected>Язык</option>';

                                                    $languages = \app\models\ContactLanguage::find()->all();
                                                    foreach ($languages as  $language) {
                                                        echo '<option value="'.$language->id.'">'.$language->slug.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_is_broadcast"><i
                                                            class="fa fa-fw fa-rss"></i></label>
                                                <select id="contact_is_broadcast" name="is_broadcast" class="form-control select2-single">
                                                    <option class="select-placeholder" disabled selected>
                                                        Рассылка
                                                    </option>
                                                    <?php
                                                    $broadcasts = \app\models\Contact::$broadcast;
                                                    foreach ($broadcasts as  $k=>$v) {
                                                        echo '<option value="'.$k.'">'.$v.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact_notification_service_id"><i
                                                            class="fa fa-fw fa-tty"></i></label>
                                                <select id="contact_notification_service_id" name="notification_service_id" class="form-control select2-single">
                                                    <option class="select-placeholder" value="" disabled selected>Способ
                                                        оповещения
                                                    </option>
                                                    <?php
                                                    $services = \app\models\ContactNotificationService::find()->all();
                                                    foreach ($services as  $service) {
                                                        echo '<option value="'.$service->id.'">'.$service->name.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
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
                                                        <option value="visit">Запланировать визит</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="contact-actions" class="panel-body">
                                            <div id="action_call" class="contact-action" style="display: none">
                                                <form id="form_action_call" action="/contacts/objectschedulecall" method="POST">
                                                    <input type="hidden" name="call_order_token" class="call_order_token">
                                                    <div class="panel panel-default block form-group" style="display: none">
                                                        <div class="row">
                                                            <div class="checkbox check-success text-left p-l-10">
                                                                <input type="checkbox" name="action_send_now" class="action_send_now" id="action_send_now_phone">
                                                                <label for="action_send_now_phone">Отправить сейчас</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group action-title">
                                                        <label>Запланировать звонок</label>
                                                    </div>
                                                    <div class="form-group input-group datepicker-content">
                                                        <label class="input-group-addon success" for=""><i class="fa fa-fw fa-calendar"></i></label>
                                                        <input name="schedule_date" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Когда применить?" />
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="checkbox check-success text-left">
                                                            <input type="checkbox" class="google-cal-show" id="google_cal_show_call">
                                                            <label for="google_cal_show_call">В Google Calendar</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group text-left" style="display: none">
                                                        <label for="action_tag_description">Описание:</label>
                                                        <textarea name="action_tag_description" id="action_tag_description" rows="10" cols="10" class="action-tag-description form-control"></textarea>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group">
                                                        <textarea name="action_comment" id="call_action-comment" rows="6" cols="10" placeholder="Комментарий" class="action-comment form-control"></textarea>
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
                                                <!---STARTOF_CLICKTOCALLBUTTON !-->
                                                <div class="acb-section audio-call-messages">
                                                    <div class="acb-message panel-body">
                                                        <!--                                                        <audio id="audio-remote" class="acb-audio"></audio>-->
                                                        <div id="callStatusOut">
                                                            <div class="callInfoOut">
                                                                <h3 id="callInfoTextOut">Ожидайте соединения</h3>
                                                                <p id="callInfoNumberOut"></p>
                                                            </div>

                                                        </div>
                                                        <!--                                                        <p class="acb-status acb-text-p">Ожидание ответа</p>-->
                                                        <div class="acb-section audio-call-actions">
                                                            <button type="button" class="btn btn-success acb-btn acb-call-btn" id="call_btn" onclick=''>Вызов</button>
                                                            <button type="button" class="btn btn-secondary acb-btn acb-hang-up-btn" id="hangup_btn" disabled>Завершить звонок</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-primary btn-block btn-audio-call" type="button">Позвонить</button>
                                                <div class="acb audio-call-block">
                                                    <div class="acb-section audio-call-sip-options">
                                                        <select name="audio-call-sip-channel" id="acb_sip_channel" class="acb-select form-control select2">
                                                            <option class="select2-choice" value="default">Выберите SIP-канал</option>
                                                            <option class="select2-choice" value="">
                                                                <?= $value ?>
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="acb-section audio-call-messages">
                                                        <div class="acb-message panel-body">
                                                            <!--                            <audio id="audio-remote" class="acb-audio"></audio>-->
                                                            <p class="acb-status acb-text-p">Ожидание ответа</p>
                                                            <p class="acb-duration acb-text-p">00:00:00</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!---ENDOF_CLICKTOCALLBUTTON !-->
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

                                            <div id="action_visit" class="contact-action" style="display: none">
                                                <form>

                                                    <div class="panel-title block">
                                                        <div class="form-group cs-select-container cs-select-block">
                                                            <select id="" class="cs-select cs-skin-slide"
                                                                    data-init-plugin="cs-select">
                                                                <option value="">Специализация врача</option>
                                                                <option value="">Специализация 1</option>
                                                                <option value="">Специализация 2</option>
                                                                <option value="">Специализация 3</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="panel-title block">
                                                        <div class="form-group cs-select-container cs-select-block">
                                                            <select id="" class="cs-select cs-skin-slide"
                                                                    data-init-plugin="cs-select">
                                                                <option value="">Отделение</option>
                                                                <option value="">Отделение 1</option>
                                                                <option value="">Отделение 2</option>
                                                                <option value="">Отделение 3</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group input-group datepicker-content">
                                                        <label class="input-group-addon success" for=""><i class="fa fa-fw fa-calendar"></i></label>
                                                        <input name="" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Дата приема" aria-required="true" aria-invalid="false">
                                                    </div>

                                                    <div class="form-group">
                                                        <button class="btn btn-info btn-block" type="button" id="visitPlanningBtn">
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

<div class="modal vp-modal" tabindex="-1" role="dialog" id="visitPlanningModal"  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="clearReservation()"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Запланировать визит</h4>
      </div>  
      <div class="modal-body">
        <div class="vp-wrap">
            <div class="vp-doctor">
                <div class="vp-date">27.12.2018</div>
                <div class="vp-calendar">
                    <div class="vp-column" data-field="doctor" data-value="Иванов" data-id="1245">
                        <div class="vp-column-head">
                            Иванов<br>
                            кардиолог
                        </div>
                        <time class="vp-time" data-time="09:00">09:00</time>
                        <time class="vp-time" data-time="09:30">09:30</time>
                        <time class="vp-time" data-time="10:00">10:00</time>
                        <time class="vp-time" data-time="10:30">10:30</time>
                        <time class="vp-time" data-time="11:00">11:00</time>
                        <time class="vp-time" data-time="11:30">11:30</time>
                        <time class="vp-time empty" data-time="12:00">12:00</time>
                        <time class="vp-time empty" data-time="12:30">12:30</time>
                    </div>
                    <div class="vp-column" data-field="doctor" data-value="Петров" data-id="2356">
                        <div class="vp-column-head">
                            Петров<br>
                            кардиолог
                        </div>
                        <time class="vp-time disable" data-time="09:00">09:00</time>
                        <time class="vp-time disable" data-time="09:30">09:30</time>
                        <time class="vp-time" data-time="10:00">10:00</time>
                        <time class="vp-time" data-time="10:30">10:30</time>
                        <time class="vp-time" data-time="11:00">11:00</time>
                        <time class="vp-time" data-time="11:30">11:30</time>
                        <time class="vp-time" data-time="12:00">12:00</time>
                        <time class="vp-time" data-time="12:30">12:30</time>
                    </div>
                    <div class="vp-column" data-field="doctor" data-value="Николаєв" data-id="1223">
                        <div class="vp-column-head">
                            Николаєв<br>
                            кардиолог
                        </div>
                        <time class="vp-time empty" data-time="09:00">09:00</time>
                        <time class="vp-time empty" data-time="09:30">09:30</time>
                        <time class="vp-time disable" data-time="10:00">10:00</time>
                        <time class="vp-time disable" data-time="10:30">10:30</time>
                        <time class="vp-time disable" data-time="11:00">11:00</time>
                        <time class="vp-time disable" data-time="11:30">11:30</time>
                        <time class="vp-time" data-time="12:00">12:00</time>
                        <time class="vp-time" data-time="12:30">12:30</time>
                    </div>
                </div>

            </div>
            <div class="vp-form">
                <form>

                    <div class="panel-title block">
                        <div class="form-group cs-select-container cs-select-block">
                            <select id="" class="cs-select cs-skin-slide"
                                    data-init-plugin="cs-select">
                                <option value="">Специализация врача</option>
                                <option value="">Специализация 1</option>
                                <option value="">Специализация 2</option>
                                <option value="">Специализация 3</option>
                            </select>
                        </div>
                    </div>

                    <div class="panel-title block">
                        <div class="form-group cs-select-container cs-select-block">
                            <select id="" class="cs-select cs-skin-slide"
                                    data-init-plugin="cs-select">
                                <option value="">Отделение</option>
                                <option value="">Отделение 1</option>
                                <option value="">Отделение 2</option>
                                <option value="">Отделение 3</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <input id="doctorName" name="doctorName" type="text" class="form-control" placeholder="Доктор (ФИО)" readonly>
                    </div>
                    
                    <div class="form-group input-group datepicker-content">
                        <label class="input-group-addon success" for=""><i class="fa fa-fw fa-calendar"></i></label>
                        <input name="" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Дата приема" aria-required="true" aria-invalid="false">
                    </div>
                    
                    <div class="form-group">
                        <input id="cabinetName" name="cabinetName" type="text" class="form-control" placeholder="Кабинет" readonly>
                    </div>
                    
                    <div class="form-group">
                        <textarea class="form-control"></textarea>
                    </div>
    
                    <input type="hidden" id="doctorId" name="doctorId">
                    <input type="hidden" id="doctorStartTime" name="doctorStartTime">
                    <input type="hidden" id="doctorEndTime" name="doctorEndTime">
                    <input type="hidden" id="cabinetId" name="cabinetId">
                    <input type="hidden" id="cabinetStartTime" name="cabinetStartTime">
                    <input type="hidden" id="cabinetEndTime" name="cabinetEndTime">

                </form>
            </div>
            <div class="vp-cabinet">
                <div class="vp-date">27.12.2018</div>
                <div class="vp-calendar">
                    <div class="vp-column" data-field="cabinet" data-value="Кабинет УЗИ" data-id="2312">
                        <div class="vp-column-head">Кабинет УЗИ</div>
                        <time class="vp-time" data-time="09:00">09:00</time>
                        <time class="vp-time" data-time="09:30">09:30</time>
                        <time class="vp-time" data-time="10:00">10:00</time>
                        <time class="vp-time" data-time="10:30">10:30</time>
                        <time class="vp-time" data-time="11:00">11:00</time>
                        <time class="vp-time" data-time="11:30">11:30</time>
                        <time class="vp-time empty" data-time="12:00">12:00</time>
                        <time class="vp-time empty" data-time="12:30">12:30</time>
                    </div>
                    <div class="vp-column" data-field="cabinet" data-value="Рентгенкабинет" data-id="1346">
                        <div class="vp-column-head">Рентгенкабинет</div>
                        <time class="vp-time disable" data-time="09:00">09:00</time>
                        <time class="vp-time disable" data-time="09:30">09:30</time>
                        <time class="vp-time" data-time="10:00">10:00</time>
                        <time class="vp-time" data-time="10:30">10:30</time>
                        <time class="vp-time" data-time="11:00">11:00</time>
                        <time class="vp-time" data-time="11:30">11:30</time>
                        <time class="vp-time" data-time="12:00">12:00</time>
                        <time class="vp-time" data-time="12:30">12:30</time>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer text-center">
        <button class="btn btn-complete" type="button">Сохранить</button>
        <button class="btn btn-default" type="button" data-dismiss="modal" onclick="clearReservation()">Отмена</button>
      </div>  
    </div>
  </div>
</div>

<script>
    var ringTone       = new window.Audio("./media/sounds/ringtone.mp3");
    var dtmfTone       = new window.Audio("./media/sounds/dtmf.wav");
    // Load Dynamically.
    var asteriskIp = 'dopomogaplus.silencatech.com';
    var asteriskUser = '600';
    var asteriskUserPass = 'AahWJQsGE7lF5d';
    var asteriskUserName = '600';
</script>
<!--<audio id="audio_remote" autoplay="autoplay"> </audio>-->
<!--<audio id="ringtone" loop src="media/sounds/ringtone.wav"> </audio>-->
<!--<audio id="ringbacktone" loop src="media/sounds/ringbacktone.wav"> </audio>-->
<!--<audio id="dtmfTone" src="media/sounds/dtmf.wav"> </audio>-->

