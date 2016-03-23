var bind_inputs = {};
var $contact_form;
var $contact_data_form;

var action_shedule_call_form_validate = {
    rules: {
        schedule_date: {dateFormat: true, required: true}
    },
    messages: {
        schedule_date: {
            dateFormat: "Дата введена не верно",
            required: "Заполните дату"
        }
    },
    submitHandler: function (form) {
        var contact_id = $('#contact-id').val();
        if (!contact_id) {
            showNotification($contact_form, 'Контакт еще не создан', 'top', 'danger', 'bar');
            return false;
        }
        $('<input />').attr('type', 'hidden')
            .attr('name', "id")
            .attr('value', $('#contact-id').val())
            .appendTo(form);
        $('<input />').attr('type', 'hidden')
            .attr('name', "_csrf")
            .attr('value', _csrf)
            .appendTo(form);
        var data = $(form).serialize();
        $.post($(form).attr('action'), data, function (response) {
            $(form).find('input[name="id"]').remove();
            $(form).find('input[name="_csrf"]').remove();
            var result = $.parseJSON(response);
            if (result.status === 200) {
                if ($(form).find('.google-cal-show').is(':checked')) {
                    var event = createGEventData('action_call', $(form).find('input[name="schedule_date"]').val());
                    createGCalEvent(event);
                }
                $contact_form.find('.history_content').append("<div class='visit' id='shedule_call_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
            }
        });
    }
};


var action_email_now_form_validate = {
    rules: {},
    messages: {},
    submitHandler: function (form) {
        var contact_id = $('#contact-id').val();
        if (!contact_id) {
            showNotification($contact_form, 'Контакт еще не создан', 'top', 'danger', 'bar');
            return false;
        }
        $('<input />').attr('type', 'hidden')
            .attr('name', "id")
            .attr('value', $('#contact-id').val())
            .appendTo(form);
        $('<input />').attr('type', 'hidden')
            .attr('name', "_csrf")
            .attr('value', _csrf)
            .appendTo(form);
        var data = $(form).serialize();
        $.post($(form).attr('action'), data, function (response) {
            var result = $.parseJSON(response);
            if (result.status === 200) {
                $contact_form.find('.history_content').append("<div class='visit' id='visit_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
            }
        });
    }
};

var action_email_date_form_validate = {
    rules: {
        schedule_date: {dateFormat: true, required: true}
    },
    messages: {
        schedule_date: {
            dateFormat: "Дата введена не верно",
            required: "Заполните дату"
        }
    },
    submitHandler: function (form) {
        var contact_id = $('#contact-id').val();
        if (!contact_id) {
            showNotification($contact_form, 'Контакт еще не создан', 'top', 'danger', 'bar');
            return false;
        }
        $('<input />').attr('type', 'hidden')
            .attr('name', "id")
            .attr('value', $('#contact-id').val())
            .appendTo(form);
        $('<input />').attr('type', 'hidden')
            .attr('name', "_csrf")
            .attr('value', _csrf)
            .appendTo(form);
        var data = $(form).serialize();
        $.post($(form).attr('action'), data, function (response) {
            $(form).find('input[name="id"]').remove();
            $(form).find('input[name="_csrf"]').remove();
            var result = $.parseJSON(response);
            if (result.status === 200) {
                if ($(form).find('.google-cal-show').is(':checked')) {
                    var event = createGEventData('action_email', $(form).find('input[name="schedule_date"]').val());
                    createGCalEvent(event);
                }
                $contact_form.find('.history_content').append("<div class='visit' id='visit_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
            }
        });
    }
};

$(function() {
    $('#action_call #form_action_call').validate(action_shedule_call_form_validate);
    $('#action_email #form_action_email_now').validate(action_email_now_form_validate);
    $('#action_email #form_action_email').validate(action_email_date_form_validate);

    $contact_form = $('#modalAddContact');
    $contact_data_form = $contact_form.find('.contact-data');

    $('input[type=text], input[type=email]', $contact_data_form).on('blur', function () {
        checkChanges($(this).attr('name'), $(this).val(), $contact_form);
    });

    //add new comment for contact
    $('#add-comment').on('click', function (e) {
        var id = $contact_form.find('#contact-id').val();
        addComment(id, $contact_form);
    });

    $('#contact-action').on('change', function (e) {
        var action = $(this).val();
        changeActionsForm(action);
    });

    $('input[name="schedule_date"]').on('blur', function (e) {
        var $form = $(this).closest('form');
        if ($form.valid()) {
            $(this).removeClass('error');
            $(this).next('.error').remove();
        }
    });



    var date = new Date();
    var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    $('.datepicker').datetimepicker({
        locale: 'ru'
        //minDate: today
    });

    $('.google-cal-show').on('change', function() {
        if ($(this).is(':checked')) {
            processGApiAuth();
        }
    });
});

function openContactForm(id) {
    buildContactForm(id, $contact_form, function () {
        bindLiveChange($contact_data_form);
    });
    $contact_form.modal({backdrop: false});
}

function openNewContactForm() {
    clearContactForm($contact_form);
    $contact_form.modal({});
    bindLiveChange($contact_data_form);
}

function clearContactForm($form) {
    $form.find('#contact-id').val('');
    $form.find('.contact-title').text('Новый контакт');
    $form.find('.history_content').empty();
    $form.find('input').val('');
    $form.find('#contact_manager_name').text('');
    $form.find('.contact-manager-name-cont').hide();
    hideNotifications($form);
}

function buildContactForm(id, $form, callback) {
    $form.find('.contact-history').find('.contract-tab, #contracts').removeClass('active');
    $form.find('.contact-history').find('.contract-tab').hide();
    $form.find('.contact-history').find('.history-tab, #history').addClass('active');
    hideNotifications($form);
    getHistory(id, $form);
    $form.find('#contact-id').val(id);
    $.getJSON('/contacts/view', {id: id}, function (response) {
        if (response.status === 200) {
            var data = response.data;
            $('#contact_manager_name').text(data.manager_name);
            $('.contact-manager-name-cont').show();
            $form.find('.contact-title').text('Контакт №' + data.int_id);
            $form.find('#contact_surname').val(data.surname);
            $form.find('#contact_name').val(data.name);
            $form.find('#contact_middle_name').val(data.middle_name);
            // var first_phone = data.first_mobile;
            // if (data.first_landline) {
            //     first_phone += ', ' + data.first_landline;
            // }
            if (data.is_deleted == 1) {
                $('.contact-deleted').show();
            } else {
                $('.contact-deleted').hide();
            }
            $form.find('#contact_phones').val(data.phones);
            $form.find('#contact_emails').val(data.emails);

            $form.find('#contact_country').val(data.country);
            $form.find('#contact_region').val(data.region);
            $form.find('#contact_area').val(data.area);
            $form.find('#contact_city').val(data.city);
            $form.find('#contact_street').val(data.street);
            $form.find('#contact_house').val(data.house);
            $form.find('#contact_flat').val(data.flat);

            callback();
        }
    });
}


function checkChanges(name, value, $form) {
    if (bind_inputs[name] !== value) {
        bind_inputs[name] = value;
        editContact(name, value, $form);
    }
}

function editContact(name, value, $form) {
    var data = {};
    $.each(bind_inputs, function (key, value) {
        data[key] = value;
    });
    //data[name] = value;
    data['_csrf'] = _csrf;
//    if (bind_inputs['id']) {
//        data['id'] = bind_inputs['id'];
//    }
    if (bind_inputs['name'] && bind_inputs['surname'] && bind_inputs['phones']) {
        $.post('/contacts/edit', data, function (response) {
            $form.find('label.error').remove();
            $form.find('.error').removeClass('error');
            var result = $.parseJSON(response);
            if (result.status == 200) {
                bind_inputs['id'] = result.data.id;
                bind_inputs[name] = value;
                $form.find('#contact-id').val(result.data.id);
                dataTable.draw(false);
                if (!data['id']) {
                    getHistory(result.data.id, $form);
                }
            }
            if (result.status == 415) {
                $.each(result.errors, function (name, errors) {
                    addError($form, name, errors);
                });

            }
            if (result.status == 412) {
                //alert(result.errors);
                showNotification('#modalAddContact', result.errors, 'top', 'danger', 'bar');
            }
            if (result.status == 403) {
                showNotification('#modalAddContact', result.errors, 'top', 'danger', 'bar', 5000);
            }
        });
    }

}


function changeActionsForm(action) {
    switch (action) {
        case "call":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_call').show();
            $contact_form.find('#contact-actions #action_schedule_date').show();
            break;
        case "email":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_email').show();
            $contact_form.find('#contact-actions #action_schedule_date').show();
            break;
        case "0":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_schedule_date').hide();
            break;
    }
}

function bindLiveChange($form) {
    $.each($('input[type=text],input[type=email]', $form), function (i, input) {
        var name = $(input).attr('name');
        var val = $(input).val();
        bind_inputs[name] = val;
    });
    var contact_id = $('#contact-id').val();
    bind_inputs['id'] = contact_id;
}


function getHistory(id, $form) {
    $form.find('.history_loader').show();
    var $content = $form.find('.history_content');
    $content.empty();
    $.getJSON('/contacts/history', {id: id}, function (response) {
        if (response.status === 200) {
            $.each(response.data, function (i, val) {
                var $item = $('<div/>');
                switch (val.type) {
                    case "show":
                        $item.addClass('show');
                        $item.attr('id', 'show_id-' + val.contact_action_id);
                        break;
                    case "visit":
                        $item.addClass('show');
                        $item.attr('id', 'object_id-' + val.contact_action_id);
                        break;
                    case "contract":
                        $item.addClass('contract');
                        $item.attr('id', 'contract_id-' + val.contact_action_id);
                        break;
                    case "scheduled_call":
                        $item.addClass('scheduled_call');
                        break;
                    case "scheduled_email":
                        $item.addClass('scheduled_email');
                        break;
                    case "payment":
                        $item.addClass('payment');
                        break;
                }
                $item.append(val.datetime + " - " + val.text);
                $content.append($item);
            });
            $form.find('.history_loader').hide();
        }
    });
}



function addComment(id, $form) {
    if (!id) {
        var message = 'Контакт еще не добавлен';
        showNotification('#modalAddContact', message, 'top', 'danger', 'bar');
        //showNotification('body',message, 'top', 'danger', 'bar');
        return false;
    }
    var $contact_comment = $form.find('#contact-comment');
    var comment_text = $contact_comment.val();
    var data = {
        id: id,
        _csrf: _csrf,
        comment: comment_text
    };
    $.post('/contacts/addcomment', data, function (response) {
        var result = $.parseJSON(response);
        if (result.status === 200) {
            $form.find('.history_content').append("<div>" + result.data.datetime + " - " + result.data.text + "</div>");
            $contact_comment.val('');
        }
    });
}

function addActionComment(id, $form) {
    if (!id) {
        var message = 'Контакт еще не добавлен';
        showNotification('#modalAddContact', message, 'top', 'danger', 'bar');
        return false;
    }
    var $contact_comment = $form.find('.action-comment');
    var comment_text = $contact_comment.val();
    var data = {
        id: id,
        _csrf: _csrf,
        comment: comment_text
    };
    $.post('/action/addcomment', data, function (response) {
        var result = $.parseJSON(response);
        if (result.status === 200) {
            // $form.find('.history_content').append("<div>" + result.data.datetime + " - " + result.data.text + "</div>");
            showNotification('#modalAddContact', 'Комментарий к действию добавлен', 'top', 'success', 'bar');
            $contact_comment.val('');
        }
    });
}

function addError($form, name, errors) {
    var $field = $form.find('[name="' + name + '"]');
    $.each(errors, function (i, error) {
        $field.addClass('error');
        $field.after("<label class='error'>" + error + "</lable>");
    });

}