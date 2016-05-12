var bind_inputs = {};
var $user_form;
var $user_data_form;

$(function() {

    $user_form = $('#modalAddUser');
    $user_data_form = $user_form.find('.user-data');

    $('input[type=text], input[type=email], input[type=password], select', $user_data_form).on('blur', function () {
        checkChanges($(this).attr('name'), $(this).val(), $user_form);
    });

    $('#user_tags', $user_data_form).on('itemAdded, itemRemoved', function () {
        $(this).data('value', $(this).val());
        bind_inputs['tags_str'] = $(this).val();
        bind_inputs['edit_tags'] = true;
        editUser($user_form);
    });

    var $tagsInput = $('#user_tags');
    $tagsInput.tagsinput({
        itemValue: 'text',
        itemText: 'text'
    });

});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('input').attr('disabled', false);
}

function openUserForm(id) {
    buildUserForm(id, $user_form, function () {
        bindLiveChange($user_data_form);
    });
    $user_form.modal({backdrop: false});
}

function openNewUserForm() {
    clearUserForm($user_form);
    $user_form.modal({});
    bindLiveChange($user_data_form);
}

function clearUserForm($form) {
    $form.find('#user-id').val('');
    $form.find('.user-title').text('Новый пользователь');
    $form.find('input').val('');
    hideNotifications($form);
}

function buildUserForm(id, $form, callback) {
    hideNotifications($form);

    $form.find('#user-id').val(id);
    $.getJSON('/users/view', {id: id}, function (response) {
        if (response.status === 200) {
            var data = response.data;
            $form.find('.user-title').text('Пользователь №' + data.int_id);
            $form.find('#user_lastname').val(data.lastname);
            $form.find('#user_firstname').val(data.firstname);
            $form.find('#user_patronymic').val(data.patronymic);

            $form.find('#user_internal').val(data.int_id);
            $form.find('#user_role').val(data.role);

            $form.find('#user_email').val(data.email);

            var $tagsInput = $('#user_tags'),
                tags_str = [];
            $tagsInput.tagsinput('removeAll');

            if (data.tags) {
                $.each(data.tags, function(tag_key, tag_value) {
                    $tagsInput.tagsinput('add', { id: tag_value.id, text: tag_value.name });
                    tags_str.push(tag_value.name);
                });
            }

            $tagsInput.attr('data-value', tags_str.join(','));

            callback();
        }
    });
}


function checkChanges(name, value, $form) {
    if (bind_inputs[name] !== value) {
        bind_inputs[name] = value;
        editUser($form, name, value);
    }
}

function editUser($form, name, value) {
    var data = {};
    $.each(bind_inputs, function (key, value) {
        data[key] = value;
    });
    //data[name] = value;
    data['_csrf'] = _csrf;
//    if (bind_inputs['id']) {
//        data['id'] = bind_inputs['id'];
//    }
    if (bind_inputs['firstname'] && bind_inputs['lastname'] && bind_inputs['patronymic'] && bind_inputs['email']) {
        $.post('/users/edit', data, function (response) {
            $form.find('label.error').remove();
            $form.find('.error').removeClass('error');
            var result = $.parseJSON(response);
            if (result.status == 200) {
                bind_inputs['id'] = result.data.id;
                bind_inputs[name] = value;
                $form.find('#user-id').val(result.data.id);
                dataTable.draw(false);
            }
            if (result.status == 415) {
                $.each(result.errors, function (name, errors) {
                    addError($form, name, errors);
                });

            }
            if (result.status == 412) {
                //alert(result.errors);
                showNotification('#modalAddUser', result.errors, 'top', 'danger', 'bar');
            }
            if (result.status == 403) {
                showNotification('#modalAddUser', result.errors, 'top', 'danger', 'bar', 5000);
            }
        });
    }

}


function bindLiveChange($form) {
    $.each($('input[type=text],input[type=email], input[type=password], select', $form), function (i, input) {
        var name = $(input).attr('name');
        var val = $(input).val();
        bind_inputs[name] = val;
    });
    var user_id = $('#user-id').val();
    bind_inputs['id'] = user_id;
}


function addError($form, name, errors) {
    var $field = $form.find('[name="' + name + '"]');
    $.each(errors, function (i, error) {
        $field.addClass('error');
        $field.after("<label class='error'>" + error + "</lable>");
    });

}