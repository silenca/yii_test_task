<?php
use app\models\User;
?>
<div class="modal fade slide-right modal-md modal-sm modal-xs"
    id="modalAddUser" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" id="user-id" value=""/>
    <div
        class="modal-dialog drop-shadow modal-md modal-sm modal-xs">
        <div class="modal-content-wrapper">
            <div class="list-view-wrapper modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-times fa-2x"></i>
                </button>
                <div class="container-xs-height full-height">
                    <div class="row-xs-height">
                        <div class="modal-body contact-modal col-middle text-center">
                            <div class="row">
                                <div
                                    class="user-data col-md-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="panel-title user-title">Новый пользователь</div>
                                            <div class="text-warning user-deleted">Контакт удален</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_internal"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="user_internal" name="int_id"
                                                       placeholder="Внутренний номер" class="form-control">
                                            </div>
	                                        <br>

	                                        <div class="input-group">
		                                        <label class="input-group-addon primary" for="user_password_sip">
			                                        <i class="fa fa-fw fa-key"></i></label>
		                                        <input type="password" id="user_password_sip" name="password_sip" placeholder="Пароль Sip"
		                                               class="form-control">
	                                        </div>
                                            <br>

                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_lastname"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="user_lastname" name="lastname"
                                                       placeholder="Фамилия пользователя" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_firstname"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="user_firstname" name="firstname"
                                                       placeholder="Имя пользователя" class="form-control">
                                            </div>
                                            <br>
                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_patronymic"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <input type="text" id="user_patronymic" name="patronymic"
                                                       placeholder="Отчество пользователя" class="form-control">
                                            </div>
                                            <br>

                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_role"><i
                                                        class="fa fa-fw fa-user"></i></label>
                                                <select id="user_role" name="role" class="form-control">
                                                    <option value="15">Администратор</option>
                                                    <option value="5">Менеджер</option>
                                                    <option value="1">Оператор</option>
                                                    <option value="10">Супевайзер</option>
                                                </select>
                                            </div>
                                            <br>

                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_email"><i
                                                        class="fa fa-fw fa-envelope"></i></label>
                                                <input type="text" id="user_email" name="email" placeholder="Email"
                                                       class="form-control">
                                            </div>
                                            <br>

                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_password"><i
                                                        class="fa fa-fw fa-unlock-alt"></i></label>
                                                <input type="password" id="user_password" name="user_password" placeholder="Новый пароль"
                                                       class="form-control">
                                            </div>
                                            <br>

                                            <div class="input-group">
                                                <label class="input-group-addon primary" for="user_password_confirm"><i
                                                        class="fa fa-fw fa-unlock-alt"></i></label>
                                                <input type="password" id="user_password_confirm" name="user_password_confirm" placeholder="Подтвердите пароль"
                                                       class="form-control">
                                            </div>

                                            <hr>
                                            <div class="form-group text-left">
                                                <input id="user_tags" name="tags_str" class="user-tags" type="text" />
                                            </div>
                                            <br>

<!--                                            <div class="form-group">-->
<!--                                                <button class="btn btn-complete btn-block" type="submit">-->
<!--                                                    Добавить-->
<!--                                                </button>-->
<!--                                            </div>-->
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