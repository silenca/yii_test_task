<?php

use app\assets\TableAsset;
use app\assets\ContactAsset;
use app\assets\DatapickerAsset;
use app\assets\SulutionFormAsset;
use app\models\User;

TableAsset::register($this);
DatapickerAsset::register($this);
ContactAsset::register($this);
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
                <div class="pull-right">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-cons pull-right" id="open-new-contact-from"><i class="fa fa-plus"></i> Добавить контакт</button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <table class="table table-hover" id="contacts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>№</th>
                            <th>Фамилия</th>
                            <th>Имя</th>
                            <th>Отчество</th>
                            <th></th>
                            <th>Телефоны</th>
                            <th>Email</th>
                            <th>Теги</th>
                            <th></th>
                            <?php if (Yii::$app->user->can('delete_contact')): ?>
                                <th>Удалить</th>
                            <?php else : ?>
                                <th></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <td></td>
                            <td><input type="text" data-column="3"  class="form-control search-input-text"></td>
                            <td><input type="text" data-column="4"  class="form-control search-input-text"></td>
                            <td><input type="text" data-column="5"  class="form-control search-input-text"></td>
                            <td></td>
                            <td><input type="text" data-column="7"  class="form-control search-input-text"></td>
                            <td><input type="text" data-column="8"  class="form-control search-input-text"></td>
                            <td><input type="text" data-column="9"  class="form-control search-input-text"></td>
                            <td></td>
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

<div class="modal fade slide-right <?= Yii::$app->user->identity->role == User::ROLE_FIN_DIR ? "modal-md" : "modal-lg" ?>" id="modalAddContact" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" id="contact-id" value=""/>
    <div class="modal-dialog drop-shadow <?= Yii::$app->user->identity->role == User::ROLE_FIN_DIR ? "modal-md" : "modal-lg" ?>">
        <div class="modal-content-wrapper">
            <div class="list-view-wrapper modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times fa-2x"></i>
                </button>
                <div class="container-xs-height full-height">
                    <div class="row-xs-height">
                        <div class="modal-body contact-modal col-middle text-center">
                            <div class="m-b-10 text-left contact-manager-name-cont">
                                <span class="label label-inverse">Ответственный - <span id="contact_manager_name"></span></span>
                            </div>
                            <div class="row">
                                <div class="contact-data <?= Yii::$app->user->identity->role == User::ROLE_FIN_DIR ? "col-md-6" : "col-md-4" ?>">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title contact-title">Новый контакт</div>
                                            <div class="text-warning contact-deleted">Контакт удален</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <div class="radio radio-star text-left primary-person">
                                                    <input type="radio" checked="checked" value="1" name="primary_person" id="primary_person_1">
                                                    <label for="primary_person_1"><i class="fa fa-star-o"></i></label>
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact-first_name"><i class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="contact-first_name" name="first_name" placeholder="Имя 1" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact-first_phone"><i class="fa fa-fw fa-phone"></i></label>
                                                <input type="text" id="contact-first_phone" name="first_phone" placeholder="Телефон"  class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="contact-first_email"><i class="fa fa-fw fa-envelope"></i></label>
                                                <input type="text" id="contact-first_email" name="first_email" placeholder="Email" class="form-control">
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <div class="radio radio-star text-left primary-person">
                                                    <input type="radio" value="2" name="primary_person" id="primary_person_2">
                                                    <label for="primary_person_2"><i class="fa fa-star-o"></i></label>
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label class="input-group-addon info" for="contact-second_name"><i class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="contact-second_name" name="second_name" placeholder="Имя 2" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon info" for="contact-second_phone"><i class="fa fa-fw fa-phone"></i></label>
                                                <input type="text" id="contact-second_phone" name="second_phone" placeholder="Телефон" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon info" for="contact-second_email"><i class="fa fa-fw fa-envelope"></i></label>
                                                <input type="text" id="contact-second_email" name="second_email" placeholder="Email" class="form-control">
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-5 text-right">Язык:</div>
                                                    <div class="col-md-7">
                                                        <div class="btn-group btn-group-xs btn-group-justified languages" data-toggle="buttons">
                                                            <label class="btn btn-default active">
                                                                <input type="radio" value="rus" name="language" id="language_rus" checked> <span class="fs-16">Рус</span>
                                                            </label>
                                                            <label class="btn btn-default">
                                                                <input type="radio" value="ukr" name="language" id="language_ukr"> <span class="fs-16">Укр</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-5 text-right">Рассылка:</div>
                                                    <div class="col-md-7">
                                                        <div class="btn-group btn-group-xs btn-group-justified distributions" data-toggle="buttons">
                                                            <label class="btn btn-default active">
                                                                <input type="radio" value="1" name="distribution" id="distribution_yes" checked> <span class="fs-16">Да</span>
                                                            </label>
                                                            <label class="btn btn-default">
                                                                <input type="radio" value="0" name="distribution" id="distribution_no"> <span class="fs-16">Нет</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="contact-history <?= Yii::$app->user->identity->role == User::ROLE_FIN_DIR ? "col-md-6" : "col-md-5" ?>">
                                    <div class="panel panel-transparent ">
                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs nav-tabs-fillup">
                                            <li class="history-tab active">
                                                <a data-toggle="tab" href="#history" aria-expanded="true"><span>История</span></a>
                                            </li>
                                            <li class="contract-tab" style="display: none">
                                                <a data-toggle="tab" href="#contracts" aria-expanded="false"><span>Контракты</span></a>
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
                                                                    <img class="history_loader image-responsive-height m-t-45 demo-mw-100" src="media/img/progress/progress.svg" alt="Progress" style="display: none">
                                                                    <div class="history_content">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <textarea id="contact-comment" rows="6" cols="10" placeholder="Комментарий" class="form-control"></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <button id="add-comment" class="btn btn-complete btn-block">Добавить</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane slide-left" id="contracts">
                                                <div class="row">
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <div class="form-group contact-contracts">
                                                                <select class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                    <option value="0">Объект (договор)</option>
                                                                </select>
                                                            </div>
                                                            <div class="contract-details" style="display: none">
                                                                <div class="form-group">
                                                                    <a class="contract-object-link" href="" target="_blank">Ссылка на объект</a>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Сумма по договору</label>
                                                                    <input type="text" class="form-control contract-total-cost" readonly="readonly">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Оплаченная сумма</label>
                                                                    <input type="text" class="form-control contract-total-paymnet" readonly="readonly">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>История платежей</label>
                                                                    <table class='table payment-table'>
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Дата</th>
                                                                                <th>Сумма</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Договор <a id="download-agreement" href="#">Скачать</a></label>
                                                                    <textarea rows="6" cols="10" placeholder="Комментарий" class="form-control contract-comment"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (Yii::$app->user->identity->role !== User::ROLE_FIN_DIR): ?>
                                    <div class="contact-actions col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="panel-title block">
                                                    <div class="form-group cs-select-container cs-select-block">
                                                        <select id="contact-action" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                            <option value="0">Действия</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="contact-actions" class="panel-body">
                                                <div id="action_show" class="contact-action" style="display: none">
                                                    <div id="build_object"></div>
                                                    <div class="form-group">
                                                        <label>Выберите объект</label>
                                                    </div>
                                                    <div class="form-group object-queue cs-select-container cs-select-block">
                                                        <select name="queue" id="object-show-queue" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                            <option value="0">Выберите очередь</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group object-house cs-select-container cs-select-block">
                                                        <select name="house" id="object-show-house" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                            <option value="0">Выберите корпус</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group object-floor cs-select-container cs-select-block">
                                                        <select name="floor" id="object-show-floor" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                            <option value="0">Выберите этаж</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group object-apartment cs-select-container cs-select-block">
                                                        <select name="apartment" id="object-show-apartment" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                            <option value="0">Выберите квартиру</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="button" value="Добавить" class="btn btn-success btn-block" id="add-action-show-object" />
                                                    </div>
                                                    <form id="form_object_show" action="/contacts/objectshow" method="POST">
                                                        <div class="form-group input-group datepicker-content">
                                                            <label class="input-group-addon success" for="contact-first_name"><i class="fa fa-fw fa-calendar"></i></label>
                                                            <input name="schedule_date" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Когда применить?" />
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox check-success text-left">
                                                                <input type="checkbox" class="google-cal-show" id="google_cal_show_object">
                                                                <label for="google_cal_show_object">В Google Calendar</label>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group">
                                                            <button class="btn btn-complete btn-block" type="submit">Сохранить</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div id="action_visit" class="contact-action" style="display: none">
                                                    <form id="form_visit_now" action="/contacts/objectvisit" method="POST">
                                                        <div class="panel panel-default block">
                                                            <div class="row">
                                                                <div class="col-md-7"><label>Визит сейчас</label></div>
                                                                <div class="col-md-5">
                                                                    <button class="btn btn-complete" type="submit">ОК</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <form id="form_visit_on_date" action="/contacts/objectvisit" method="POST">
                                                        <div class="form-group input-group datepicker-content">
                                                            <label class="input-group-addon success" for="contact-first_name"><i class="fa fa-fw fa-calendar"></i></label>
                                                            <input name="schedule_date" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Когда применить?" />
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox check-success text-left">
                                                                <input type="checkbox" class="google-cal-show" id="google_cal_show_visit">
                                                                <label for="google_cal_show_visit">В Google Calendar</label>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group">
                                                            <button class="btn btn-complete btn-block" type="submit">Добавить</button>
                                                        </div>

                                                    </form>
                                                </div>
                                                <div id="action_contract" class="contact-action" style="display: none">
                                                    <form action="/contacts/objectcontract" method="POST" enctype="multipart/form-data">
                                                        <div class="form-group contact-revision-contracts cs-select-container cs-select-block">
                                                            <select name="contract" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                <option value="0">Договор №</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group form-group-default required text-left">
                                                            <label>Установить цену</label>
                                                            <input type="number" name="price" class="form-control object-price" required="" aria-required="true" aria-invalid="true">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Выберите объект</label>
                                                        </div>
                                                        <div class="form-group object-queue cs-select-container cs-select-block">
                                                            <select name="queue" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                <option value="0">Выберите очередь</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group object-house cs-select-container cs-select-block">
                                                            <select name="house" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                <option value="0">Выберите корпус</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group object-floor cs-select-container cs-select-block">
                                                            <select name="floor" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                <option value="0">Выберите этаж</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group object-apartment cs-select-container cs-select-block">
                                                            <select name="apartment" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                <option value="0">Выберите квартиру</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <button class="btn btn-success btn-block btn-xs upload" type="button">
                                                                <i class="fa fa-cloud-upload"></i>
                                                                <span class="bold">Прикрепить договор</span>
                                                            </button>
                                                            <input name="UploadDoc[docFile]" type="file" class="form-control object-agreement" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="display: none"/>
                                                        </div>
                                                        <div class="form-group selected_file" style="display: none">
                                                            <button class="btn btn-primary btn-block btn-xs" type="button">
                                                                <span class="filename"></span>
                                                                <i class="pg-close fs-14"></i>
                                                            </button>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group">
                                                            <button class="btn btn-complete btn-block" type="submit">На согласование</button>
                                                        </div>

                                                    </form>
                                                </div>
                                                <div id="action_call" class="contact-action" style="display: none">
                                                    <form id="form_action_call" action="/contacts/objectschedulecall" method="POST">
                                                        <div class="form-group">
                                                            <label>Запланировать звонок</label>
                                                        </div>
                                                        <div class="form-group input-group datepicker-content">
                                                            <label class="input-group-addon success" for="contact-first_name"><i class="fa fa-fw fa-calendar"></i></label>
                                                            <input name="schedule_date" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Когда применить?" />
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox check-success text-left">
                                                                <input type="checkbox" class="google-cal-show" id="google_cal_show_call">
                                                                <label for="google_cal_show_call">В Google Calendar</label>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group">
                                                            <button class="btn btn-complete btn-block" type="submit">Добавить</button>
                                                        </div>

                                                    </form>
                                                </div>
                                                <div id="action_email" class="contact-action" style="display: none">
                                                    <form id="form_action_email_now" action="/contacts/objectscheduleemail" method="POST">
                                                        <div class="panel panel-default block">
                                                            <div class="row">
                                                                <div class="col-md-8"><label>Отправить сейчас</label></div>
                                                                <div class="col-md-4">
                                                                    <button class="btn btn-complete" type="submit">ОК</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <form id="form_action_email" action="/contacts/objectscheduleemail" method="POST">
                                                        <div class="form-group">
                                                            <label>Запланировать Email</label>
                                                        </div>
                                                        <div class="form-group input-group datepicker-content">
                                                            <label class="input-group-addon success" for="contact-first_name"><i class="fa fa-fw fa-calendar"></i></label>
                                                            <input name="schedule_date" type="text" class="form-control object-schedule-datetime datepicker" placeholder="Когда применить?" />
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox check-success text-left">
                                                                <input type="checkbox" class="google-cal-show" id="google_cal_show_email">
                                                                <label for="google_cal_show_email">В Google Calendar</label>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group">
                                                            <button class="btn btn-complete btn-block" type="submit">Добавить</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div id="action_payment" class="contact-action" style="display: none">
                                                    <form id="form_action_payment" action="/contracts/payment" method="POST">
                                                        <div class="form-group">
                                                            <label>Платеж</label>
                                                        </div>
                                                        <div class="form-group contact-approved-contracts cs-select-container cs-select-block">
                                                            <select name="contract" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                                                <option value="0">Объект (договор)</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group form-group-default required text-left">
                                                            <label>Сумма платежа</label>
                                                            <input type="number" name="amount" class="form-control payment-amount" required="" aria-required="true" aria-invalid="true" min="0">
                                                        </div>
                                                        <div class="form-group">
                                                            <textarea name="comment" rows="6" cols="10" placeholder="Комментарий" class="form-control payment-comment"></textarea>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group">
                                                            <button class="btn btn-complete btn-block" type="submit">Добавить</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!--<div id="solution_status" class="solution_status"></div>-->
                                        <?php /* if (Yii::$app->user->can('contracts')): ?>
                                          <div id="solution_form"></div>
                                          <?php endif; */ ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
