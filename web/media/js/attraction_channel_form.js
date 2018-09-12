/**
 *
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */
var $attraction_channel_form;
var $attraction_channel_data_form;
var bind_inputs = {};

function openNewAttractionChannelForm() {
    clearAttractionChannel($attraction_channel_form);
    $attraction_channel_form.modal({});
    bindLiveChange($attraction_channel_form);
}

function openAttractionChannelForm(id) {
    buildAttractionChannelForm(id, $attraction_channel_form, function () {
        bindLiveChange($attraction_channel_data_form);
    });
    $attraction_channel_form.modal({backdrop: false});
}

function bindLiveChange($form) {
    $.each($('input[type=text],input[type=email]', $form), function (i, input) {
        var name = $(input).attr('name');
        if (name) {
            bind_inputs[name] = $(input).val()+'' ;
        }
    });
    bind_inputs['id'] = $('#attraction-channel-id').val();
}

function clearAttractionChannel($form) {
    $form.find('.attraction-channel-title').text('Новый SIP Канал');
    $form.find('#attraction_channel_phone_number').val('');
    $form.find('#attraction_channel_host').val('');
    $form.find('#attraction_channel_port').val('');
    $form.find('#attraction_channel_login').val('');
    $form.find('#attraction_channel_password').val('');
    hideNotifications($form);
}

function checkChanges(name, value, $form) {
    if (bind_inputs[name] !== value) {
        bind_inputs[name] = value;
        editAttractionChannel($form, name, value);
    }
}

function addError($form, name, errors) {
    var $field = $form.find('[name="' + name + '"]');
    $.each(errors, function (i, error) {
        $field.addClass('error');
        $field.after("<label class='error'>" + error + "</lable>");
    });
}

function buildAttractionChannelForm(id, $form, callback) {
    hideNotifications($form);

    $form.find('#attraction-channel-id').val(id);
    $.getJSON('/attraction-channel/view', {id: id}, function (response) {
        if (response.status === 200) {
            var data = response.data;
            $form.find('.attraction-channel-title').text('SIP Канал №' + data.id);
            $form.find('#attraction_channel_phone_number').val(data.phone_number);
            $form.find('#attraction_channel_host').val(data.host);
            $form.find('#attraction_channel_port').val(data.port);

            $form.find('#attraction_channel_login').val(data.login);
            $form.find('#attraction_channel_password').val(data.password);

            callback();
        }
    });
}

function editAttractionChannel($form, name, value) {
    var data = {};
    $.each(bind_inputs, function (key, value) {
        if (value != "undefined")
            data[key] = value;
    });
    data['_csrf'] = _csrf;
    console.log(bind_inputs);
    if (bind_inputs['phone_number'] && bind_inputs['host'] && bind_inputs['port'] && bind_inputs['login'] && bind_inputs['password']) {
        $.post('/attraction-channel/edit', data, function (response) {
            $form.find('label.error').remove();
            $form.find('.error').removeClass('error');
            var result = $.parseJSON(response);
            if (result.status == 200) {
                bind_inputs['id'] = result.data.id;
                if (name && value) {
                    bind_inputs[name] = value;
                }
                $form.find('#attraction-channel-id').val(result.data.id);
                if (typeof dataTable !== 'undefined') { dataTable.draw(false); }
                if (typeof tagContactsdataTable !== 'undefined') { tagContactsdataTable.columns(0).search($('#contacts_list').val()).draw(); }
                if (typeof contactsModaldataTable !== 'undefined') { contactsModaldataTable.columns().search('').draw(); }
                // if (!data['id']) {
                //     getHistory(result.data.id, $form);
                // }
            }
            if (result.status == 415) {
                $.each(result.errors, function (name, errors) {
                    addError($form, name, errors);
                });

            }
            if (result.status == 412) {
                //alert(result.errors);
                showNotification('#modalAddAttractionChannel', result.errors, 'top', 'danger', 'bar');
            }
            if (result.status == 403) {
                showNotification('#modalAddAttractionChannel', result.errors, 'top', 'danger', 'bar', 5000);
            }
        });
    }

}


$(function() {
    $attraction_channel_form = $('#modalAddAttractionChannel');
    $attraction_channel_data_form = $attraction_channel_form.find('.attraction-channel-data');

    $('input[type=text], input[type=email]', $attraction_channel_data_form).on('blur', function () {
        $(this).data('value', $(this).val());
        checkChanges($(this).attr('name'), $(this).data('value'), $attraction_channel_form);
    });
});