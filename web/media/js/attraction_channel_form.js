/**
 *
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */
var $attraction_channel_form;
var $attraction_channel_data_form;
var bind_inputs = {};

var sendTimer;

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
    $.each($('input[type=text],input[type=email],input[type=checkbox],select', $form), function (i, input) {
        var name = $(input).attr('name');
        if (name) {
            if($(input).attr('type')=='checkbox') {
                if($(input).prop('checked')) {
                    bind_inputs[name] = '1';
                } else {
                    bind_inputs[name] = '0';
                }
            } else {
                if($(input).val() == null)
                    bind_inputs[name] = 'undefined' ;
                else
                    bind_inputs[name] = $(input).val()+'' ;

            }

        }
    });
    bind_inputs['id'] = $('#attraction-channel-id').val();
}

function clearAttractionChannel($form) {
    $form.find('.attraction-channel-title').text('Новый Канал Привлечения');
    // var active = $form.find('#attraction_channel_active');
    $form.find('#attraction_channel_active').off('change',checkboxEvent);
    if(switchers['attraction_channel_active'] != undefined) {
        setSwitchery(switchers['attraction_channel_active'], false);
    }
    $form.find('#attraction_channel_active').on('change',checkboxEvent);
    $form.find('#attraction_channel_name').val('');
    $form.find('#attraction-channel-id').val('');
    bind_inputs['id'] = "undefined";
    $form.find('#attraction_channel_type option.select-placeholder').prop('selected',true);
    updateSipChannelsList($form);
    // $form.find('#attraction_channel_sip_channel_id option.select-placeholder').prop('selected',true);

    $form.find('#attraction_channel_integration_type option.select-placeholder').prop('selected',true);

    $form.find('#sip_channel_group').hide();
    $form.find('#integration_type_group').hide();
    $form.find('#attraction_channel_type').off('change').on('change',function () {
        $form.find('#sip_channel_group').hide();
        $form.find('#integration_type_group').hide();
        switch ($(this).val()) {
            case '1': $form.find('#sip_channel_group').show();break;
            case '2': $form.find('#integration_type_group').show();break;
        }
    });

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


function updateSipChannelsList($form,selected) {
    var select = $form.find('#attraction_channel_sip_channel_id');
    $.getJSON('/attraction-channel/get-free-sip-channels',{},function (response) {
        var list = response.data;
        select.empty();//.append('<option class="select-placeholder" value="" disabled selected>SIP Канал</option>');
        for(K in list) {
            select.append('<option value="'+list[K].id+'">'+list[K].phone_number+'</option>');
        }
    });
}

function buildAttractionChannelForm(id, $form, callback) {
    hideNotifications($form);

    $form.find('#attraction-channel-id').val(id);
    $.getJSON('/attraction-channel/view', {id: id}, function (response) {
        if (response.status === 200) {
            var data = response.data;
            $form.find('.attraction-channel-title').text('Канал привлечения №' + data.id);
            if(switchers['attraction_channel_active'] != undefined) {
                $form.find('#attraction_channel_active').off('change',checkboxEvent);
                if(data.is_active == "1")
                    setSwitchery(switchers['attraction_channel_active'], true);
                else
                    setSwitchery(switchers['attraction_channel_active'], false);
                $form.find('#attraction_channel_active').on('change',checkboxEvent);
            }
            $form.find('#attraction_channel_name').val(data.name);
            if(data.type) {
                $form.find('#attraction_channel_type option[value="'+data.type+'"]').prop('selected',true);
            }
            if(data.sipChannels) {
                $form.find('#attraction_channel_sip_channel_id option[value="'+data.sip_channel_id+'"]').prop('selected',true);
            }
            if(data.integration_type) {
                $form.find('#attraction_channel_integration_type option[value="'+data.integration_type+'"]').prop('selected',true);
            }
            $form.find('#sip_channel_group').hide();
            $form.find('#integration_type_group').hide();
            switch ($form.find('#attraction_channel_type').val()) {
                case '1': $form.find('#sip_channel_group').show();break;
                case '2': $form.find('#integration_type_group').show();break;
            }
            $form.find('#attraction_channel_type').off('change').on('change',function () {
                $form.find('#sip_channel_group').hide();
                $form.find('#integration_type_group').hide();
                switch ($(this).val()) {
                    case '1': $form.find('#sip_channel_group').show();break;
                    case '2': $form.find('#integration_type_group').show();break;
                }
            });

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
    if (bind_inputs['name'] && bind_inputs['type']
        && (bind_inputs['type']=='0' || (bind_inputs['type']=='1' && bind_inputs['sip_channel_id']!="undefined")
        || (bind_inputs['type']=='2' && bind_inputs['integration_type']!="undefined"))) {
        $.post('/attraction-channel/edit', data, function (response) {
            clearTimeout(sendTimer);
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
function checkboxEvent() {
    $attraction_channel_form = $('#modalAddAttractionChannel');
    $attraction_channel_data_form = $attraction_channel_form.find('.attraction-channel-data');
    var input = $(this);
    if (input.prop('checked')) {
        input.data('value', '1');
    } else {
        input.data('value', '0');
    }
    if(sendTimer != undefined){
        clearTimeout(sendTimer);
    }
    sendTimer = setTimeout( checkChanges,500,input.attr('name'),input.data('value'),$attraction_channel_form);
}

$(function() {
    $attraction_channel_form = $('#modalAddAttractionChannel');
    $attraction_channel_data_form = $attraction_channel_form.find('.attraction-channel-data');
    $attraction_channel_form.find('#attraction_channel_sip_channel_id').select2({
        placeholder: "SIP Канал",
        allowClear: true,
        width: '100%'
    });

    $('input[type=text], input[type=email], input[type=checkbox],select', $attraction_channel_data_form).on('blur', function () {
        $(this).data('value', $(this).val());
        if(sendTimer != undefined){
            clearTimeout(sendTimer);
        }
        sendTimer = setTimeout( checkChanges,500,$(this).attr('name'), $(this).data('value'), $attraction_channel_form);
    });
});