var dataTable;
var bind_inputs = {};
var action;
var $contact_form;
var payment_access = false;
var contract_comment;


var action_contract_form_validate = {
    rules: {
        price: {required: true},
        queue: {valueNotZero: true},
        house: {valueNotZero: true},
        floor: {valueNotZero: true},
        apartment: {valueNotZero: true},
        'UploadDoc[docFile]': {required: true}
    },
    messages: {
        price: {required: "Поле не должно быть пустым"},
        queue: {valueNotZero: "Выберите очередь"},
        house: {valueNotZero: "Выберите корпус"},
        floor: {valueNotZero: "Выберите этаж"},
        apartment: {valueNotZero: "Выберите квартиру"},
        'UploadDoc[docFile]': {required: "Прикрепите договор"}
    },
    errorPlacement: function (error, element) {
        if ($(element).prop('nodeName') == 'SELECT') {
            $(element).closest('.cs-select-container').append(error);
        } else {
            error.insertAfter(element);
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
                .attr('value', contact_id)
                .appendTo(form);
        $('<input />').attr('type', 'hidden')
                .attr('name', "_csrf")
                .attr('value', _csrf)
                .appendTo(form);

        var data = new FormData();
        var $agreement = $(form).find('.object-agreement');
        data.append('UploadDoc[docFile]', $agreement[0].files[0]);
        data.append('id', contact_id);
        //data.append('queue', $(form).find('.object-queue select').val());
        //data.append('house', $(form).find('.object-house select').val());
        //data.append('floor', $(form).find('.object-floor select').val());
        data.append('apartment', $(form).find('.object-apartment select').val());
        data.append('price', $(form).find('.object-price').val());
        data.append('_csrf', _csrf);
        if ($(form).find('.contact-revision-contracts select').val() != 0) {
            data.append('contract', $(form).find('.contact-revision-contracts select').val());
        }
        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                var result = $.parseJSON(response);
                if (result.status === 200) {
                    $contact_form.find('.history_content').append("<div class='contract' id='contract_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
                    buildActions($contact_form.find('.contact-actions'), true);

                }
            }
        });
    }
};

var action_visit_now_form_validate = {
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

var action_visit_date_form_validate = {
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
                    var event = createGEventData('visit_on_date', $(form).find('input[name="schedule_date"]').val());
                    createGCalEvent(event);
                }
                $contact_form.find('.history_content').append("<div class='visit' id='visit_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
            }
        });
    }
};
var action_show_form_validate = {
    rules: {
        schedule_date: {dateFormat: true}
    },
    messages: {
        schedule_date: {dateFormat: "Дата введена не верно"},
    },
    errorPlacement: function (error, element) {
        if ($(element).prop('nodeName') == 'SELECT') {
            $(element).closest('.cs-select-container').append(error);
        } else {
            error.insertAfter(element);
        }
    },
    submitHandler: function (form) {
        var contact_id = $('#contact-id').val();
        if (!contact_id) {
            showNotification($contact_form, 'Контакт еще не создан', 'top', 'danger', 'bar');
            return false;
        }
        if (!$(form).find('.apartment').length) {
            showNotification($contact_form, 'Добавьте объект', 'top', 'danger', 'bar');
            return false;
        }
        $('<input />').attr('type', 'hidden')
                .attr('name', "id")
                .attr('value', contact_id)
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
                    var event = createGEventData('object_show', $(form).find('input[name="schedule_date"]').val());
                    createGCalEvent(event);
                }
                $contact_form.find('.history_content').append("<div class='show' id='show_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
                clearBuildObjects($contact_form.find('#action_show'));
                clearHouseDropDown($contact_form.find('#action_show'));
                clearFloorDropDown($contact_form.find('#action_show'));
                clearApartmentDropDown($contact_form.find('#action_show'));
                $(form).find('input[name="schedule_date"]').val('');
            }
        });
    }
};
var action_add_show_form_validate = {
    rules: {
        queue: {valueNotZero: true},
        house: {valueNotZero: true},
        floor: {valueNotZero: true},
        apartment: {valueNotZero: true}
    },
    messages: {
        queue: {valueNotZero: "Выберите очередь"},
        house: {valueNotZero: "Выберите корпус"},
        floor: {valueNotZero: "Выберите этаж"},
        apartment: {valueNotZero: "Выберите квартиру"}
    },
    errorPlacement: function (error, element) {
        if ($(element).prop('nodeName') == 'SELECT') {
            $(element).closest('.cs-select-container').append(error);
        } else {
            error.insertAfter(element);
        }
    }
};

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

var action_payment_form_validate = {
    rules: {
        price: {required: true},
        contract: {valueNotZero: true},
    },
    messages: {
        price: {required: "Поле не должно быть пустым"},
        contract: {valueNotZero: "Выберите объект"},
    },
    errorPlacement: function (error, element) {
        if ($(element).prop('nodeName') == 'SELECT') {
            $(element).closest('.cs-select-container').append(error);
        } else {
            error.insertAfter(element);
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
                .attr('value', contact_id)
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
                $contact_form.find('.history_content').append("<div class='payment' id='payment_id-" + result.data.id + "'>" + result.data.system_date + " - " + result.data.history + "</div>");
                $(form).find('.payment-amount').val('');
                $(form).find('.payment-comment').val('');
                var contract_id = $(form).find('select').val();
                buildPayments(contact_id, contract_id);
            }
        });
    }
};

$(function () {
    $('#action_contract form').on('submit', function(e) {
        if ($(this).find('.contact-revision-contracts select').val() != 0) {
            $contact_form.find('#action_contract form input[name="UploadDoc[docFile]"]').rules('remove','required');
        } else {
            $contact_form.find('#action_contract form input[name="UploadDoc[docFile]"]').rules('add','required');
        }
    });

    
    $('#action_show form').validate(action_show_form_validate);
    $('#action_contract form').validate(action_contract_form_validate);
    $('#action_visit #form_visit_now').validate(action_visit_now_form_validate);
    $('#action_visit #form_visit_on_date').validate(action_visit_date_form_validate);
    $('#action_call #form_action_call').validate(action_shedule_call_form_validate);
    $('#action_email #form_action_email_now').validate(action_email_now_form_validate);
    $('#action_email #form_action_email').validate(action_email_date_form_validate);
    $('#action_payment #form_action_payment').validate(action_payment_form_validate);


    var initTable = function () {
        var table = $('#contacts-table');

        var settings = {
            "sDom": "<'table-responsive't><'row'<p i>>",
            "sPaginationType": "bootstrap",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
                "sLengthMenu": "_MENU_ ",
                "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
            "iDisplayLength": 20,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                url: "/contacts/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {
                    "targets": [5, 9, 10],
                    //"data": null,
                    "orderable": false,
                    //"defaultContent": '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>'
                },
                {"visible": false, "targets": [0]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
                $(row).addClass('open-link');
            }
        };
        dataTable = table.DataTable(settings);

        var $searchBoxes = $('input.search-input-text, select.search-input-select');

        $('.search-input-text').on('keyup', function () {   // for text boxes
            delay(function(){
                $.each($searchBoxes, function (index, val) {
                    var i = $(this).attr('data-column');
                    var v = $(this).val();
                    if (v.length > 2 || v.length == 0) dataTable.columns(i).search(v);
                });
                dataTable.draw();
            }, 2000 );
        });

        $('.search-input-select').on('change', function () {   // for select box
            $.each($searchBoxes, function (index, val) {
                var i = $(this).attr('data-column');
                var v = $(this).val();
                dataTable.columns(i).search(v);
            });
            dataTable.draw();
        });
    };


    //evenets on contact page
    if ($('#contacts-table').length) {
        var $contact_table = $('#contacts-table');
        $contact_form = $('#modalAddContact');
        var $contact_data_form = $contact_form.find('.contact-data');

        openContactByUrl($contact_data_form);

        //open form
        $contact_table.on('click', 'tr', function (e) {
            if (!$(this).parent('thead').length && !$(this).find('.dataTables_empty').length) {
                $contact_form.modal({backdrop: false});
                var id = $(this).data('id');
                buildContactForm(id, $contact_form, function () {
                    bindLiveChange($contact_data_form);
                });
            }
        });

        //open new form
        $('#open-new-contact-from').on('click', function (e) {
            clearContactForm($contact_form);
            $contact_form.modal({});
            buildActions($contact_form.find('.contact-actions'), false);
            bindLiveChange($contact_data_form);
        });

        //remove contact
        $contact_table.on('click', '.remove', function (e) {
            e.stopPropagation();
            if (confirm('Вы действительно хотите удалить ?')) {
                var $tr = $(this).closest('tr');
                var id = $tr.data('id');
                $.post('/contacts/delete', {id: id, _csrf: _csrf}, function (response) {
                    var result = $.parseJSON(response);
                    if (result.status === 200) {
                        //initTable();
                        dataTable.row($tr).remove().draw(false);
                        //$tr.empty().remove();
                    }
                });
            }
        });

        //add object for show
        $('#add-action-show-object').on('click', function () {
            var error = false;
            var queue, house, floor, apartment, object_id;
            if ($('#object-show-queue').val() === '0') {
                var $queue_container = $('#object-show-queue').closest('.cs-select-container');
                $queue_container.find('.error').remove();
                $queue_container.append($("<label class='error'></label>").text('Выберите очередь'));
                error = true;
            } else {
                queue = $('#object-show-queue option:selected').text();
            }
            if ($('#object-show-house').val() === '0') {
                var $house_container = $('#object-show-house').closest('.cs-select-container');
                $house_container.find('.error').remove();
                $house_container.append($("<label class='error'></label>").text('Выберите корпус'));
                error = true;
            } else {
                house = $('#object-show-house option:selected').text();
            }
            if ($('#object-show-floor').val() === '0') {
                var $house_container = $('#object-show-floor').closest('.cs-select-container');
                $house_container.find('.error').remove();
                $house_container.append($("<label class='error'></label>").text('Выберите этаж'));
                error = true;
            } else {
                floor = $('#object-show-floor option:selected').text();
            }
            if ($('#object-show-apartment').val() === '0') {
                var $house_container = $('#object-show-apartment').closest('.cs-select-container');
                $house_container.find('.error').remove();
                $house_container.append($("<label class='error'></label>").text('Выберите квартиру'));
                error = true;
            } else {
                apartment = $('#object-show-apartment option:selected').text();
                object_id = $('#object-show-apartment').val();
            }
            if (!error) {
                if (!$contact_form.find('#contact-actions #action_show form .apartment[value="' + object_id + '"]').length) {
                    var build_object_content = $([
                        "<div class='panel panel-default'>",
                        "   <div>",
                        "       <button type='button' class='close' aria-hidden='true'><i class='pg-close fs-14'></i></button>",
                        "   </div>",
                        "   <div class='panel-body'>",
                        "       <div class='row'>",
                        "           <div class='build_object'></div>",
                        "       </div>",
                        "   </div>",
                        "</div>"
                    ].join("\n"));
                    build_object_content.find('.build_object').html("<b>Очередь</b>: " + queue + ", <b>корпус</b>: " + house + ", <b>этаж</b>: " + floor + ", <b>квартира</b>: " + apartment);
                    $('#build_object').append(build_object_content);
                    $contact_form.find('#contact-actions #action_show form').append("<input class='apartment' type='hidden' name='apartment[]' value='" + object_id + "' />");
                }
            }
        });

        //remove build object for show
        $('#build_object').on('click', '.close', function (e) {
            e.stopPropagation();
            $(this).closest('.panel').empty().remove();
        });

        //add new comment for contact
        $('#add-comment').on('click', function (e) {
            var id = $contact_form.find('#contact-id').val();
            addComment(id, $contact_form);
        });

        $contact_form.on('hidden.bs.modal', function () {
            removeContactFromUrl();
        });

        $('#contact-action').on('change', function (e) {
            action = $(this).val();
            changeActionsForm(action);
        });

        $contact_table.on('click', 'a', function (e) {
            e.stopPropagation();
        });

        $('input[name="schedule_date"]').on('blur', function (e) {
            var $form = $(this).closest('form');
            if ($form.valid()) {
                $(this).removeClass('error');
                $(this).next('.error').remove();
            }
        });

        $('.upload', $contact_form).on('click', function () {
            $contact_form.find('input[type=file].object-agreement').trigger('click');
        });

        $('input[type=file].object-agreement', $contact_form).change(function () {
            var file = $(this)[0].files[0];
            if (file) {
                $(this).removeClass('error');
                $(this).next('.error').remove();
            }
            var $selected_file = $contact_form.find('.selected_file');
            $selected_file.find('span').text(file.name);
            $selected_file.show();
            $contact_form.find('.upload').hide();
        });

        $('.selected_file i', $contact_form).on('click', function () {
            clearAgreementFile();
        });

        $('input[type=text], input[type=email]', $contact_data_form).on('blur', function () {
            checkChanges($(this).attr('name'), $(this).val(), $contact_form);
        });

        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        $('.datepicker').datetimepicker({
            locale: 'ru'
            //minDate: today
        });

        initTable();

        $('body').on('click', '.alert .alert-link a', function (e) {
            e.preventDefault();
            openContactByUrl($contact_data_form, $(this).attr('href'));
        });

        $('table').on('click', '.more', function (e) {
            e.stopPropagation();
            var $cont = $(this).closest('td').find('.additional');
            if ($cont.is(':visible')) {
                $cont.addClass('hide');
            } else {
                $cont.removeClass('hide');
            }
        });

        //on change contract-comment
        $contact_form.find('#contracts').on('change', '.contract-comment', function () {
            var text = $(this).val();
            if (text != contract_comment) {
                contract_comment = text;
                var id = $contact_form.find('#contact-id').val();
                saveContractComment(id);
            }
        });
        //download agreement
        $contact_form.find('#contracts').on('click', '#download-agreement', function (e) {
            e.preventDefault();
            var id = $contact_form.find('#contact-id').val();
            downloadAgreement(id);
        });

        $('.google-cal-show').on('change', function() {
            if ($(this).is(':checked')) {
                processGApiAuth();
            }
        });
    }

});

function openContactByUrl($form, link) {
    var contact_id;
    var contact_phone;
    var url;
    if (link) {
        url = link;
    } else {
        url = window.location.href;
    }
    var regular = /(contact=|number=)[0-9]+/;
    var found = url.match(regular);
    if (found && found[0]) {
        var param = found[0];
        var pos = param.indexOf("contact=");
        if (pos !== -1) {
            var sub = param.substr(pos);
            contact_id = sub.substr(8);
            var pos = sub.indexOf('&');
            $contact_form.modal({});
            buildContactForm(contact_id, $contact_form, function () {
                bindLiveChange($form);
            });
        } else {
            pos = param.indexOf("number=");
            var sub = param.substr(pos);
            contact_phone = sub.substr(7);
            $contact_form.modal({});
            $.getJSON('/contacts/get-contact-by-phone', {phone: contact_phone}, function (response) {
                if (response.status === 200) {
                    var data = response.data;
                    buildContactForm(data.contact_id, $contact_form, function () {
                        bindLiveChange($form);
                    });
                } else {
                    clearContactForm($contact_form);
                    $form.find('#contact-first_phone').val(contact_phone);
                    bindLiveChange($form);
                }
            });
        }
    }
    return false;
}

function removeContactFromUrl() {
    var url = window.location.href;
    if (url.indexOf("#contact=") !== -1) {
        window.history.pushState("", "Contacts", "/contacts");
    }
    return false;
}

function changeActionsForm(action) {
    switch (action) {
        case "show":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_show').show();
            $contact_form.find('#contact-actions #action_schedule_date').show();
            getQueue($('#action_show'));
            break;
        case "visit":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_visit').show();
            $contact_form.find('#contact-actions #action_schedule_date').hide();
            break;
        case "contract":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_contract').show();
            $contact_form.find('#contact-actions #action_schedule_date').show();
            getQueue($('#action_contract'));
            break;
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
        case "payment":
            $contact_form.find('#contact-actions .contact-action').hide();
            $contact_form.find('#contact-actions #action_payment').show();
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

function buildContactForm(id, $form, callback) {
    $form.find('.contact-history').find('.contract-tab, #contracts').removeClass('active');
    $form.find('.contact-history').find('.contract-tab').hide();
    $form.find('.contact-history').find('.history-tab, #history').addClass('active');
    hideNotifications($form);
    getHistory(id, $form);
    $form.find('#contact-id').val(id);
    //clearContracts($form);
    clearPayments();
    $.getJSON('/contacts/view', {id: id}, function (response) {
        if (response.status === 200) {
            var data = response.data;
            $('#contact_manager_name').text(data.manager_name);
            $('.contact-manager-name-cont').show();
            buildContracts(id);
            $form.find('.contact-title').text('Контакт №' + data.int_id);
            $form.find('#contact-first_name').val(data.first_name);
            payment_access = data.payment_access;
            if (payment_access) {
                $form.find('.contact-history').find('.contract-tab').show();
            }
            var first_phone = data.first_mobile;
            if (data.first_landline) {
                first_phone += ', ' + data.first_landline;
            }
            if (data.is_deleted == 1) {
                $('.contact-deleted').show();
            } else {
                $('.contact-deleted').hide();
            }
            $form.find('#contact-first_phone').val(first_phone);
            $form.find('#contact-first_email').val(data.first_email);
            $form.find('#contact-second_name').val(data.second_name);
            var second_phone = data.second_mobile;
            if (data.second_landline) {
                second_phone += ', ' + data.second_landline;
            }
            $form.find('#contact-second_phone').val(second_phone);
            $form.find('#contact-second_email').val(data.second_email);
            switch (data.language) {
                case 'rus':
                    $form.find('#language_rus').prop("checked", true);
                    $form.find('#language_rus').closest('label').addClass('active').siblings('label').removeClass('active');
                    break;
                case 'ukr':
                    $form.find('#language_ukr').prop("checked", true);
                    $form.find('#language_ukr').closest('label').addClass('active').siblings('label').removeClass('active');
                    break;
            }
            if (data.distribution == '1') {
                $form.find('#distribution_yes').prop("checked", true);
                $form.find('#distribution_yes').closest('label').addClass('active').siblings('label').removeClass('active');
            } else {
                $form.find('#distribution_no').prop("checked", true);
                $form.find('#distribution_no').closest('label').addClass('active').siblings('label').removeClass('active');
            }
            $('#primary_person_' + data.primary_person).prop('checked', true);

            triggerChoise_CsSelect($form.find('#contact-channel_attraction'), data.channel_attraction_id);
            var contract_id = false;
            if (data.notConfirmedContract.length > 0) {
                contract_id = data.notConfirmedContract[0].id;
            }
            buildActions($contact_form.find('.contact-actions'), contract_id);
            callback();
        }
    });
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

function clearContactForm($form) {
    $form.find('#contact-id').val('');
    $form.find('.contact-title').text('Новый контакт');
    $form.find('.history_content').empty();
    $form.find('input').val('');
    $form.find('#contact_manager_name').text('');
    $form.find('.contact-manager-name-cont').hide();
    hideNotifications($form);
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

//get objects
function getQueue($form) {
    $.getJSON('/object/getqueue', function (json) {
        buildQueue(json.data, $form, null);
    });
}

function getHousing(queue, $form) {
    $.getJSON('/object/gethousing', {queue: queue}, function (json) {
        buildHousing(json.data, $form, null);
    });
}

function getFloor(house, $form) {
    $.getJSON('/object/getfloor', {house: house}, function (json) {
        buildFloor(json.data, $form, null);
    });
}

function getApartment(floor, $form) {
    $.getJSON('/object/getapartment', {floor: floor}, function (json) {
        buildApartment(json.data, $form, null);
    });
}

function buildQueue(data, $form, selected) {
    var options = [];
    options[0] = 'Выберите очередь';
    $.each(data, function (i, val) {
        options[val.id] = val.queue;
    });
    var $el = $form.find('.object-queue');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    //$el.empty();
    $el.prepend((new ExtList({
        options: options,
        selected: selected,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
    $el.find('select').on('change', function () {
        var queue = $(this).val();
        if (queue != '0') {
            $el.find('.error').hide();
            getHousing(queue, $form);
            clearFloorDropDown($form);
            clearApartmentDropDown($form);
        }
    });
}

function buildHousing(data, $form, selected) {
    var options = [];
    options[0] = 'Выберите корпус';
    $.each(data, function (i, val) {
        options[val.id] = val.housing;
    });
    var $el = $form.find('.object-house');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    $el.prepend((new ExtList({
        options: options,
        selected: selected,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
    $el.find('select').on('change', function () {
        var house = $(this).val();
        if (house != '0') {
            $el.find('.error').hide();
            getFloor(house, $form);
            clearApartmentDropDown($form);
        }
    });
}

function buildFloor(data, $form, selected) {
    var options = [];
    options[0] = 'Выберите этаж';
    $.each(data, function (i, val) {
        options[val.id] = val.floor;
    });
    var $el = $form.find('.object-floor');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();

    $el.prepend((new ExtList({
        options: options,
        selected: selected,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
    $el.find('select').on('change', function () {
        var floor = $(this).val();
        if (floor != '0') {
            $el.find('.error').hide();
            getApartment(floor, $form);
        }
        //$form.find('form').valid();
    });
}

function buildApartment(data, $form, selected) {
    var options = [];
    options[0] = 'Выберите квартиру';
    $.each(data, function (i, val) {
        options[val.id] = val.number;
    });
    var $el = $form.find('.object-apartment');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    $el.prepend((new ExtList({
        options: options,
        selected: selected,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
    $el.find('select').on('change', function () {
        $el.find('.error').hide();
    });
}

function clearQueueDropDown($form) {
    var $el = $form.find('.object-queue');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    var options = [];
    options[0] = 'Выберите очередь';
    $el.prepend((new ExtList({
        options: options,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
}
function clearHouseDropDown($form) {
    var $el = $form.find('.object-house');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    var options = [];
    options[0] = 'Выберите корпус';
    $el.prepend((new ExtList({
        options: options,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
}
function clearFloorDropDown($form) {
    var $el = $form.find('.object-floor');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    var options = [];
    options[0] = 'Выберите этаж';
    $el.prepend((new ExtList({
        options: options,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
}
function clearApartmentDropDown($form) {
    var $el = $form.find('.object-apartment');
    var el_name = $el.find('select').attr('name');
    var el_id = $el.find('select').attr('id');
    $el.find('.cs-wrapper').empty().remove();
    var options = [];
    options[0] = 'Выберите квартиру';
    $el.prepend((new ExtList({
        options: options,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: {id: el_id}
    })).init());
}

function clearAgreementFile() {
    var $file_input = $contact_form.find('input[type=file].object-agreement');
    $file_input.replaceWith($file_input = $file_input.clone(true));
    $contact_form.find('.selected_file').hide();
    $contact_form.find('.upload').show();
}

function clearBuildObjects($content) {
    $content.find('#build_object').empty();
    $content.find('form .apartment').remove();
}

function buildActions($content, contract_id) {
    $content.find('.contact-action').hide();
    var options = [];
    var attributes = [];
    options[0] = 'Действия';
    options['show'] = 'Показ объекта';
    options['visit'] = 'Визит';
    options['call'] = 'Звонок';
    options['email'] = 'Email';
    options['contract'] = 'Договор';
    options['payment'] = 'Платеж';
    var $el = $content.find('#contact-action').closest('.cs-select-container');
    var el_name = $el.find('select').attr('name');
    $el.find('.cs-wrapper').empty().remove();
    attributes['id'] = 'contact-action';
    $el.prepend((new ExtList({
        options: options,
        classes: 'cs-select cs-skin-slide',
        name: el_name,
        attr: attributes
    })).init());
    $el.find('select').on('change', function () {
        changeActionsForm($(this).val());
    });
}

function buildContracts(contact_id) {
    //Строим среднюю форму, контракты
    $.get('/contracts/get-by-contact-id', {id: contact_id}, function (response) {
        var result = $.parseJSON(response);
        var options = [];
        options[0] = 'Объект (договор)';

        var $el = $contact_form.find('.contact-contracts');
        var el_name = $el.find('select').attr('name');
        var el_id = $el.find('select').attr('id');
        $el.find('.cs-wrapper').empty().remove();
        if (Object.keys(result.data).length > 0) {
            $.each(result.data, function (i, val) {
                options[i] = val;
            });
        }
        $el.prepend((new ExtList({
            options: options,
            classes: 'cs-select cs-skin-slide',
            name: el_name,
            attr: {id: el_id}
        })).init());
        $el.find('select').on('change', function () {
            buildContractData(contact_id, $(this).val());
        });
    });

    //Строим вкладку "Платеж"
    $.get('/contracts/get-by-contact-id', {id: contact_id, type: 'approved'}, function (response) {
        var result = $.parseJSON(response);
        var options = [];
        options[0] = 'Объект (договор)';

        var $el = $contact_form.find('.contact-approved-contracts');
        var el_name = $el.find('select').attr('name');
        var el_id = $el.find('select').attr('id');
        $el.find('.cs-wrapper').empty().remove();
        if (Object.keys(result.data).length > 0) {
            $.each(result.data, function (i, val) {
                options[i] = val;
            });
        }
        $el.prepend((new ExtList({
            options: options,
            classes: 'cs-select cs-skin-slide',
            name: el_name,
            attr: {id: el_id}
        })).init());
        $el.find('select').on('change', function () {
            $el.find('.error').hide();
            buildPayments(contact_id, $(this).val());
        });
    });

    //Строим вкладку "Договор"
    $.get('/contracts/get-by-contact-id', {id: contact_id, type: 'revision'}, function (response) {
        var result = $.parseJSON(response);
        var options = [];
        options[0] = 'Договор №';

        var $el = $contact_form.find('.contact-revision-contracts');
        var el_name = $el.find('select').attr('name');
        var el_id = $el.find('select').attr('id');
        $el.find('.cs-wrapper').empty().remove();
        if (Object.keys(result.data).length > 0) {
            $.each(result.data, function (i, val) {
                options[i] = val;
            });
        }
        $el.prepend((new ExtList({
            options: options,
            classes: 'cs-select cs-skin-slide',
            name: el_name,
            attr: {id: el_id}
        })).init());
        $el.find('select').on('change', function () {
            $el.find('.error').hide();
            buildRevisionContracts($(this).val());
        });
    });
}

function buildPayments(id, contract_id) {
    if (payment_access) {
        clearPayments();
        $.get('/contracts/get-payments', {id: id, contract_id: contract_id}, function (response) {
            var result = $.parseJSON(response);
            if (result.status === 200) {
                var payment_table = $([
                    "<table class='table payment-table'>",
                    "   <thead>",
                    "       <tr>",
                    "           <th>Дата</th>",
                    "           <th>Сумма</th>",
                    "       </tr>",
                    "   </thead>",
                    "   <tbody>",
                    "   </tbody>",
                    "</table>"
                ].join("\n"));
                $.each(result.data, function (i, val) {
                    payment_table.find('tbody').append("<tr><td>" + val.date + "</td><td>" + val.amount + "</td></tr>");
                });
                $contact_form.find('#action_payment').append(payment_table);
            }
        });
    }
}

function buildRevisionContracts(contract_id) {
    $.get('/contracts/get-details-by-contact-id', {contract_id: contract_id}, function (response) {
        var result = $.parseJSON(response);
        var contract_action_content = $contact_form.find('#action_contract');
        if (result.status === 200) {
            var data = {
                queue: result.data.queue,
                house: result.data.house,
                floor: result.data.floor
            };
            $.get('/object/get-all-fields', data, function (response) {
                var object_result = $.parseJSON(response);
                var queue_options = [];
                //Build queues
                contract_action_content.find('select[name="queue"] option').each(function (i, val) {
                    queue_options[i] = {
                        id: $(val).val(),
                        queue: $(val).text()
                    };
                });
                var housing_options = [];
                $.each(object_result.data.houses, function (i, val) {
                    housing_options[i] = {
                        id: val.id,
                        housing: val.housing
                    };
                });
                var floors_options = [];
                $.each(object_result.data.floors, function (i, val) {
                    floors_options[i] = {
                        id: val.id,
                        floor: val.floor
                    };
                });
                var apartment_options = [];
                $.each(object_result.data.apartments, function (i, val) {
                    apartment_options[i] = {
                        id: val.id,
                        number: val.number
                    };
                });
                buildQueue(queue_options, contract_action_content, result.data.queue);
                buildHousing(housing_options, contract_action_content, result.data.house);
                buildFloor(floors_options, contract_action_content, result.data.floor);
                buildApartment(apartment_options, contract_action_content, result.data.apartment);
            });
            contract_action_content.find('input[name="price"]').val(result.data.price);
            //contract_action_content.find('.object-agreement').val('http://www.someimage.com/image.gif');
            contract_action_content.find('.upload').hide();
            contract_action_content.find('.selected_file').show();
            contract_action_content.find('.selected_file .filename').text("Договор");
        }
    });
}

function buildContractData(contact_id, contract_id) {
    var contract_content = $contact_form.find('#contracts .contract-details');
    contract_content.hide();
    if (payment_access && contract_id != 0) {
        $.get('/contracts/get-payments-by-contact-id', {id: contact_id, contract_id: contract_id}, function (response) {
            var result = $.parseJSON(response);
            if (result.status === 200) {
                contract_content.find('.contract-object-link').attr('href', result.data.link);
                contract_content.find('.contract-total-cost').val(result.data.total_cost);
                contract_content.find('.contract-total-paymnet').val(result.data.total_payment);
                contract_content.find('.contract-comment').val(result.data.comment);
                contract_comment = result.data.comment;
                var payment_table = contract_content.find('.payment-table');
                payment_table.find('tbody').empty();
                if (result.data.payments) {
                    $.each(result.data.payments, function (i, val) {
                        payment_table.find('tbody').append("<tr><td>" + val.date + "</td><td>" + val.amount + "</td></tr>");
                    });
                }
                contract_content.show();
            }
        });
    }
}

function saveContractComment(contact_id) {
    var contract_id = $('.contact-contracts select').val();
    var data = {
        _csrf: _csrf,
        contract_id: contract_id,
        comment: contract_comment
    };
    $.post('/contracts/comment?id=' + contact_id, data);
}

function downloadAgreement(contact_id) {
    var contract_id = $('.contact-contracts select').val();
    window.open('/contracts/download?id=' + contact_id + '&contract_id=' + contract_id);
}

function clearPayments() {
    $contact_form.find('#action_payment').find('table.payment-table').empty().remove();
}

function addError($form, name, errors) {
    var $field = $form.find('[name="' + name + '"]');
    $.each(errors, function (i, error) {
        $field.addClass('error');
        $field.after("<label class='error'>" + error + "</lable>");
    });

}