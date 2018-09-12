/**
 *
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

var $sip_channel_form;
var $sip_channel_data_form;
var bind_inputs = {};

function openNewSipChannelForm() {
    clearSipChannel($sip_channel_form);
    $sip_channel_form.modal({});
    bindLiveChange($sip_channel_form);
}

function openSipChannelForm(id) {
    buildSipChannelForm(id, $sip_channel_form, function () {
        bindLiveChange($sip_channel_data_form);
    });
    $sip_channel_form.modal({backdrop: false});
}

function bindLiveChange($form) {
    $.each($('input[type=text],input[type=email]', $form), function (i, input) {
        var name = $(input).attr('name');
        if (name) {
            bind_inputs[name] = $(input).val()+'' ;
        }
    });
    bind_inputs['id'] = $('#sip-channel-id').val();
}

function clearSipChannel($form) {
    $form.find('.sip-channel-title').text('Новый SIP Канал');
    $form.find('#sip_channel_phone_number').val('');
    $form.find('#sip_channel_host').val('');
    $form.find('#sip_channel_port').val('');
    $form.find('#sip_channel_login').val('');
    $form.find('#sip_channel_password').val('');
    hideNotifications($form);
}

function checkChanges(name, value, $form) {
    if (bind_inputs[name] !== value) {
        bind_inputs[name] = value;
        editSipChannel($form, name, value);
    }
}

function addError($form, name, errors) {
    var $field = $form.find('[name="' + name + '"]');
    $.each(errors, function (i, error) {
        $field.addClass('error');
        $field.after("<label class='error'>" + error + "</lable>");
    });
}

function buildSipChannelForm(id, $form, callback) {
    hideNotifications($form);

    $form.find('#sip-channel-id').val(id);
    $.getJSON('/sip-channel/view', {id: id}, function (response) {
        if (response.status === 200) {
            var data = response.data;
            $form.find('.sip-channel-title').text('SIP Канал №' + data.id);
            $form.find('#sip_channel_phone_number').val(data.phone_number);
            $form.find('#sip_channel_host').val(data.host);
            $form.find('#sip_channel_port').val(data.port);

            $form.find('#sip_channel_login').val(data.login);
            $form.find('#sip_channel_password').val(data.password);

            callback();
        }
    });
}

function editSipChannel($form, name, value) {
    var data = {};
    $.each(bind_inputs, function (key, value) {
        if (value != "undefined")
            data[key] = value;
    });
    data['_csrf'] = _csrf;
    console.log(bind_inputs);
    if (bind_inputs['phone_number'] && bind_inputs['host'] && bind_inputs['port'] && bind_inputs['login'] && bind_inputs['password']) {
        $.post('/sip-channel/edit', data, function (response) {
            $form.find('label.error').remove();
            $form.find('.error').removeClass('error');
            var result = $.parseJSON(response);
            if (result.status == 200) {
                bind_inputs['id'] = result.data.id;
                if (name && value) {
                    bind_inputs[name] = value;
                }
                $form.find('#sip-channel-id').val(result.data.id);
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
                showNotification('#modalAddSipChannel', result.errors, 'top', 'danger', 'bar');
            }
            if (result.status == 403) {
                showNotification('#modalAddSipChannel', result.errors, 'top', 'danger', 'bar', 5000);
            }
        });
    }

}


$(function() {
    $sip_channel_form = $('#modalAddSipChannel');
    $sip_channel_data_form = $sip_channel_form.find('.sip-channel-data');

    $('input[type=text], input[type=email]', $sip_channel_data_form).on('blur', function () {
        $(this).data('value', $(this).val());
        checkChanges($(this).attr('name'), $(this).data('value'), $sip_channel_form);
    });
});