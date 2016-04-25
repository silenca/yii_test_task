var $tagContactsTable, $contactsModalTable;
var tagContactsdataTable, contactsModaldataTable;
var tagSelect;
var tagUsersSelect;
var contacts;

$(function () {
    $tagContactsTable = $('#tag_contacts_table');

    var $tagForm = $('#tag_form'),
        $tagId = $('#tag_id'),
        $tagSelectBox = $('#tag_search_select'),
        $tagUsersSelectBox = $('#tag_users_select'),
        $tagToggle = $('#tag_toggle'),
        $tagAdd = $('.tag-add'),
        $tagStartDate = $('#tag_start_date'),
        $tagSubmit = $('#tag_submit'),
        $addContactTable = $('#add_contact_table'),
        $tagName = $('#tag_name'),
        $contactsList = $('#contacts_list'),
        $contactsCounter = $('#ring_counter');

    var initTagContactsTable = function () {
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
                url: "/tags/getdata", // json datasource
                type: "GET", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            'fnDrawCallback': function(data) {
                var count = data.json.contact_count;
                $contactsCounter.find('span:first').text(count.count_called);
                $contactsCounter.find('span:nth-child(2)').text(count.count_all);
                $contactsCounter.find('span:last').text(count.count_all - count.count_called);
                // $('#ring_counter span:nth-child(2)')
                console.log(data.json);
            },
            "ordering": false,
            "columnDefs": [
                {"visible": false, "targets": [0]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
            }
        };

        tagContactsdataTable = $tagContactsTable.DataTable(settings);
    };


    if ($tagContactsTable.length) {
        initTagContactsTable();
    }

    var show_columns = columns.filter(function(item) {
        return hide_columns.indexOf(item) === -1;
    });


    var initContactsModalTable = function () {
        var $contactsModalTable = $('#contacts-table');

        var settings = {
            "sDom": "<'table-responsive't><'row'<p i>>",
            "sPaginationType": "bootstrap",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
                "sLengthMenu": "_MENU_ ",
                "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
            "iDisplayLength": 5,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                url: "/tags/getcontacts", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"visible": false, "targets": [show_columns.indexOf('id')]},
                {"orderable": false, "targets": []}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[show_columns.indexOf('id')]);
                $(row).addClass('open-link');
            },
            'fnDrawCallback': function() {
                $('input[name="contacts[]"]').each(function() {
                    // console.log($(this).val());
                    if ($.inArray($(this).val(), contacts) != -1) {
                        $(this).prop('checked', true);
                    }
                })
            }
        };
        $.each(show_columns, function(col_index, col_val) {
            settings.columnDefs.push({ "name": col_val, "targets": col_index });
        });

        $.each(columns, function(col_index, col_val) {
            if (!columns_full[col_val]['orderable']) {
                settings.columnDefs[1].targets.push(col_index);
            }
        });

        $.each(hide_columns, function(i ,val) {
            var index = columns.indexOf(val);
            settings.columnDefs[0].targets.push(index);
        });

        contactsModaldataTable = $contactsModalTable.DataTable(settings);

        contactsModaldataTable.on( 'xhr', function () {
            var json = contactsModaldataTable.ajax.json();
            contacts = json.contacts;


        } );

        var $searchBoxes = $('input.search-input-text, select.search-input-select');

        $('.search-input-text').on('keyup', function () {   // for text boxes
            delay(function () {
                $.each($searchBoxes, function (index, val) {
                    var n = $(this).attr('data-column');
                    var v = $(this).val();
                    var strLenDef = 2;
                    if (n == 'city' || n == 'street' || n == 'house' || n == 'flat') {
                        strLenDef = 0;
                    }
                    if (v.length > strLenDef || v.length == 0) {
                        contactsModaldataTable.columns(n+':name').search(v);
                    }
                });
                contactsModaldataTable.draw();
            }, 2000);
        });

        $('.search-input-select').on('change', function () {   // for select box
            $.each($searchBoxes, function (index, val) {
                var n = $(this).attr('data-column');
                var v = $(this).val();
                contactsModaldataTable.columns(n+':name').search(v);
            });
            contactsModaldataTable.draw();
        });
    };

    if (userRole !== 'operator') {
        initContactsModalTable();
    }

    var tagSelectOpts = {
            placeholder: "Имя тега",
            ajax: {
                url: "/tags/gettags",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function (data, params) {
                    var items = data.data.items;

                    // data formatting
                    $.map(items, function(item, i) {
                        items[i].as_task = item.as_task == 1;
                    });

                    return {
                        results: items
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 2,
            allowClear: true,
            theme: "default", // "classic"
            debug: true  //TODO remove this later
        },
        tagUsersSelectOpts = {
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            theme: "default", // "classic"
            debug: true,  //TODO remove this later
            multiple: true
        };

    //init selects
    tagSelect = $tagSelectBox.select2(tagSelectOpts);
    tagUsersSelect = $tagUsersSelectBox.select2(tagUsersSelectOpts);

    userScenario(userRole);

    // fill users select
    $.get('/tags/getusers', function (response) {
        var result = $.parseJSON(response),
            items = result.data.items;
        $.map(items, function(item, i) {
            items[i].text = item.firstname;
        });
        if (result.status === 200) {
            tagUsersSelect.select2({data: result.data.items, placeholder: "Имя пользователя", allowClear: true});
        } else {

        }
    });

    //events on tags page
    $tagToggle.on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            startDate = moment().format('YYYY-MM-DD HH:mm:ss'),
            tagNameDate = moment().format('DD/MM/YYYY');

        if (!$this.hasClass('create_tag-active')) {
            manageTagData('clear');
            tagSelect.val(null).trigger("change");
            tagSelect.select2("destroy").attr('disabled', true).hide();
            $tagAdd.show().find('input').attr('disabled', false);
            $tagStartDate.val(startDate);
            $tagName.val(tagNameDate + ' ');
            $this.text('Поиск тегов');
        } else {
            manageTagData('clear');
            tagSelect.select2(tagSelectOpts).attr('disabled', false).show();
            $tagAdd.hide().find('input').val('').attr('disabled', true);
            $this.text('Создать тег');
        }

        $this.toggleClass('create_tag-active');
    });

    $tagSubmit.on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var data = prepareData($tagForm);
        $.post('/tags/edit', data, function (response) {
            var result = $.parseJSON(response);
            if (result.status === 200) {
                showNotification('.content', 'Данные сохранены', 'top', 'success', 'bar', 5000);
                $tagId.val(result.data.id);
            } else if(result.status === 415) {
                var error_txt = '';
                $.each(result.errors, function (name, errors) {
                    error_txt += name + ' - ' + errors;
                });
                showNotification('.content', error_txt, 'top', 'danger', 'bar', 125000);
            } else {
                showNotification('.content', 'Ошибка сервера', 'top', 'danger', 'bar', 125000);
            }
        });
    });

    $addContactTable.on('click', function(e) {
        if ($('.tag-name:enabled').val() == '') {
            alert('Выберите тег');
        } else {
            $('#modalAddContactToTag').modal();
            $('.search-input-text').val('');
            contactsModaldataTable.search( '' )
                .columns().search( '' ).draw();
        }

        e.preventDefault();
    });

    $('#add_contact_csv').on('click', function(e) {
        if ($tagSelectBox.val() == '') {
            alert('Выберите тег');
        } else {
            $('#modalImportCsv').modal();
        }
        e.preventDefault();
    });

    var contacts = [];

    $('#add_contact').on('click', function(e) {
        // var contacts_line = '';
        // contacts.forEach(function(element, index, array) {
        //     contacts_line += element + ','
        // });
        // contacts_line = contacts_line.slice(0,-1);
        // $('input[name="contacts_list"]').val(contacts_line);

        // Объединение скрытого поля и отфильтрованных контактов
        var hidden_arr = $contactsList.val().split(',');
        var contacts_arr = contacts.split(',');

        var concat_arr = hidden_arr.concat(contacts_arr);
        var result_arr = concat_arr.filter(function (item, pos) {return concat_arr.indexOf(item) == pos});

        var result = result_arr.join(',');
        $contactsList.val(result);

        $contactsList.trigger('change');
        //contactsModaldataTable.columns().draw();
        $('#modalAddContactToTag').modal('hide');
        // console.log(contacts_line);

        // if (tagSelect.is(':enabled')) {
        //     $('#tag_submit').trigger('click');
        // }
    });

    $(document.body).on('change', 'input[name="contacts[]"]', function() {
        if($(this).is(':checked')) {
            contacts.push($(this).val());
        } else {
            delete contacts[contacts.indexOf($(this).val())];
        }
    });

    tagSelect.on('select2:open', function(evt) {
        var eventParams = evt.params;
    });

    tagSelect.on('change', function (e) {
        manageTagData('clear');
        if ($(this).val() != '') {
            $tagSubmit.removeClass('disabled');
        } else {
            $tagSubmit.addClass('disabled');
        }
    });

    $tagName.on('keyup', function(e) {
        if ($(this).val() != '') {
            $tagSubmit.removeClass('disabled');
        } else {
            $tagSubmit.addClass('disabled');
        }
    });

    tagSelect.on('select2:select', function(evt) {
        var eventParams = evt.params,
            data = eventParams.data;
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                data[key] = data[key] == null ? '' : data[key];
            }
        }
        manageTagData('fill', data);
    });

    $contactsList.on('change', function (e) {
        tagContactsdataTable.columns(0).search($(this).val()).draw();
        contacts = $(this).val().split(',');
        //contactsModaldataTable.columns().search('').draw();
    });

    $(document).on('click', '#tag_contacts_table .contact-phone', function(e) {
        var contactId = $(this).parents('tr').data('id'),
            phone = $(this).data('phone');
        openContactForm(contactId);
        initCallNow(phone);
        initRingRound($tagForm);
    });

    $(document).on('click', '#contacts-table .contact-tags', function(e) {
        var tag_name = $(this).text();
        $('.search-input-text[data-column="tags"]').val(tag_name);
        contactsModaldataTable.columns('tags:name').search(tag_name).draw();
    });
});

function prepareData($form) {
    var data = {};
    $.each($form.find('input[type="text"]:enabled, textarea:enabled'), function (i, el) {
        var elName = $(el).attr('name');
        data[elName] = $(el).val();
    });
    if (tagSelect.val() != '') {
        data.name = tagSelect.select2('data')[0].text;
        data.id = tagSelect.val();
    } else {
        data.id = $('#tag_id').val();
    }
    data.tag_users = tagUsersSelect.val();

    // if ($('#contacts_list').val() != "") {
    //     data.tag_contacts = $('#contacts_list').val().split(',');
    // }
    data.tag_contacts = $('#contacts_list').val().split(',');
    data.as_task = $('#tag_as_task').is(':checked') ? 1 : 0;
    data._csrf = _csrf;
    return data;
}

function select2Search ($el, term) {
    $el.select2('open');

    // Get the search box within the dropdown or the selection
    // Dropdown = single, Selection = multiple
    var $search = $el.data('select2').dropdown.$search || $el.data('select2').selection.$search;
    // This is undocumented and may change in the future

    $search.val(term);
    $search.trigger('keyup');
}

function manageTagData(action, data) {
    var $description = $('#tag_description'),
        $script = $('#tag_script'),
        $as_task = $('#tag_as_task'),
        $contactsList = $('#contacts_list');
    switch (action) {
        case 'fill':
            $description.val(data.description);
            $script.val(data.script);
            tagUsersSelect.val(data.tag_users).trigger("change");
            $as_task.prop('checked', data.as_task);
            $contactsList.val(data.tag_contacts).trigger('change');
            break;
        case 'clear':
            $description.val('');
            $script.val('');
            tagUsersSelect.val([]).trigger("change");
            $as_task.prop('checked', false);
            $contactsList.val('').trigger('change');
            break;
    }
}

function userScenario(userRole) {
    var $description = $('#tag_description'),
        $script = $('#tag_script'),
        $as_task = $('#tag_as_task'),
        $contactsList = $('#contacts_list'),
        $tagToggle = $('#tag_toggle'),
        $tagSubmit = $('#tag_submit'),
        $addContacts = $('.add-contacts');
    switch (userRole) {
        case 'operator':
            $tagToggle.hide();
            tagUsersSelect.attr('disabled', true);
            $as_task.parent().hide();
            $tagSubmit.parent().hide();
            $description.attr('disabled', true);
            $script.parent().hide();
            $addContacts.hide();
            tagContactsdataTable.columns(2).visible(false);
            break;
    }
}
