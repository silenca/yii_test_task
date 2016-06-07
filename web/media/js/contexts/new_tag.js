var tagContactsdataTable;
var tagTableFilters = {};
var contactTableFilters = {};
var tagUsers = [];
var contactTableInitialized = false;

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
            return {
                results: data.data.items
            };
        },
        cache: true
    },
    escapeMarkup: function (markup) {
        return markup;
    }, // let our custom formatter work
    minimumInputLength: 2,
    allowClear: true,
    theme: "default", // "classic"
    debug: true  //TODO remove this later
};
var tagUsersSelectOpts = {
    escapeMarkup: function (markup) {
        return markup;
    }, // let our custom formatter work
    theme: "default", // "classic"
    debug: true,  //TODO remove this later
    multiple: true
};
var columnDefs = [{"visible": false, "targets": [0]}];
if (userRole == 'operator') {
    columnDefs.push({"visible": false, "targets": [2]});
}

var initTagContactsTable = function () {
    var settings = {
        "sDom": "<'table-responsive't><'row'<p i>>",
        //"sPaginationType": "bootstrap",
        "destroy": true,
        "scrollCollapse": true,
        "oLanguage": {
            "sLengthMenu": "_MENU_ ",
            "sInfo": "Showing <b>_START_ to _END_</b>",
            "sInfoFiltered": ""
        },
        "iDisplayLength": -1,
        // "bPaginate": false,
        // "paging": false,
        "processing": true,
        "serverSide": true,
        "bPaginate": false,
        //"bInfo": false,
        "order": [],
        "ajax": {
            url: "/tags/getdata", // json datasource
            type: "POST", // method
            error: function () {  // error handling
                //alert('error data');
            },
            "data": function (d) {
                d._csrf = $('meta[name="csrf-token"]').attr('content');
            }
        },
        'fnDrawCallback': function (data) {
            var count = data.json.contact_count;
            $('#ring_counter').find('span:first').text(count.count_called);
            $('#ring_counter').find('span:nth-child(2)').text(count.count_all);
            $('#ring_counter').find('span:last').text(count.count_all - count.count_called);
        },
        "ordering": false,
        "columnDefs": columnDefs,
        "createdRow": function (row, data, index) {
            $(row).attr('data-id', data[0]);
        },
    };

    tagContactsdataTable = $('#tag_contacts_table').DataTable(settings);

    var $searchBoxes = $('#tag_contacts_table').find('input.search-input-text, select.search-input-select');

    $.each($searchBoxes, function () {
        var column = $(this).data('column_name');
        tagTableFilters[column] = $(this).val();
    });

    $('#tag_contacts_table .search-input-text').on('keyup', function () {   // for text boxes
        delay(function () {
            $.each($searchBoxes, function (index, val) {
                var i = $(this).attr('data-column');
                var n = $(this).attr('data-column_name');
                var v = $(this).val();
                if (v.length >= 0) {
                    tagContactsdataTable.columns(i).search(v);
                }
                tagTableFilters[n] = v;
            });
            tagContactsdataTable.draw();
        }, 1000);
    });

    $('#tag_contacts_table .search-input-select').on('change', function () {   // for select box
        $.each($searchBoxes, function (index, val) {
            var i = $(this).attr('data-column');
            var n = $(this).attr('data-column_name');
            var v = $(this).val();
            tagContactsdataTable.columns(i).search(v);
            tagTableFilters[n] = v;
        });
        tagContactsdataTable.draw();
    });
};

var show_columns = columns.filter(function (item) {
    return hide_columns.indexOf(item) === -1;
});

var initContactsModalTable = function (tag_id) {
    var $contactsModalTable = $('#contacts-table');
    var $searchBoxes = $contactsModalTable.find('input.search-input-text, select.search-input-select');
    var settings = {
        "sDom": "<'table-responsive't><'row'<p i>>",
        "sPaginationType": "bootstrap",
        "destroy": true,
        "scrollCollapse": true,
        "oLanguage": {
            "sLengthMenu": "_MENU_ ",
            "sInfo": "",
            "sInfoFiltered": ""
        },
        "iDisplayLength": 10,
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "/tags/get-contacts-another-tag?tag_id=" + tag_id, // json datasource
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
        'fnDrawCallback': function () {
            $.each($searchBoxes, function () {
                var column = $(this).attr('data-column');
                contactTableFilters[column] = $(this).val();
            });
        }
    };
    $.each(show_columns, function (col_index, col_val) {
        settings.columnDefs.push({"name": col_val, "targets": col_index});
    });

    $.each(columns, function (col_index, col_val) {
        if (!columns_full[col_val]['orderable']) {
            settings.columnDefs[1].targets.push(col_index);
        }
    });

    $.each(hide_columns, function (i, val) {
        var index = columns.indexOf(val);
        if (index >=0) {
            settings.columnDefs[0].targets.push(index);
        }
    });

    contactsModaldataTable = $contactsModalTable.DataTable(settings);

    contactTableInitialized = true;

    $('#contacts-table .search-input-text').on('keyup', function () {   // for text boxes
        delay(function () {
            $.each($searchBoxes, function (index, val) {
                var n = $(this).attr('data-column');
                var v = $(this).val();
                var strLenDef = 2;
                if (n == 'city' || n == 'street' || n == 'house' || n == 'flat') {
                    strLenDef = 0;
                }
                if (v.length > strLenDef || v.length == 0) {
                    contactsModaldataTable.columns(n + ':name').search(v);
                }
                contactTableFilters[n] = v;
            });
            contactsModaldataTable.draw();
        }, 2000);
    });

    $('#contacts-table .search-input-select').on('change', function () {   // for select box
        $.each($searchBoxes, function (index, val) {
            var n = $(this).attr('data-column');
            var v = $(this).val();
            contactsModaldataTable.columns(n + ':name').search(v);
            contactTableFilters[n] = v;
        });

        contactsModaldataTable.draw();
    });
};

$(function () {
    $('#tag_search_select').select2(tagSelectOpts);
    $('#tag_search_select').on('select2:select', function (evt) {
        tagUsers = evt.params.data.users;
        buildDataByTag(evt.params.data);
        contactTableInitialized = false;
    });

    $('#tag_search_select').on('change', function () {
        if ($(this).val() != '') {
            if (userRole != 'operator') {
                $('#tag_submit').removeClass('disabled');
                $('#export_csv').removeClass('disabled');
                $('#add_contact_csv').removeClass('disabled');
                $('#add_contact_table').removeClass('disabled');
                $('#tag_users_select').attr('disabled', false);
                $('#tag_as_task').attr('disabled', false);
                $('#tag_description').attr('readonly', false);
                $('#tag_script').attr('readonly', false);
            }
            $('#update_contacts_table').removeClass('disabled');
        } else {
            clearTagData();
            $('#tag_submit').addClass('disabled');
            $('#export_csv').addClass('disabled');
            $('#add_contact_csv').addClass('disabled');
            $('#add_contact_table').addClass('disabled');
            $('#update_contacts_table').addClass('disabled');
            $('#tag_users_select').attr('disabled', true);
            $('#tag_as_task').attr('disabled', true);
            $('#tag_description').attr('readonly', true);
            $('#tag_script').attr('readonly', true);
        }
    });

    $('form#exportCsv').on('submit', function(e) {
        $(this).find('input[name="tag_id"]').val($('#tag_search_select').val());
        $(this).find('input[name="manager_id"]').val(tagTableFilters.manager_id !== '0'?tagTableFilters.manager_id: null);
        $(this).find('input[name="status"]').val(tagTableFilters.status !== '0'?tagTableFilters.status: null);
        $(this).find('input[name="comment"]').val(tagTableFilters.comment.length?tableFilters.comment: null);
        $(this).find('input[name="attitude_level"]').val(tagTableFilters.attitude_level !== '0'?tagTableFilters.attitude_level: null);
        return true;
    });

    $('#tag_users_select').on('change', function () {
        if ($('#tag_search_select').val()) {
            var tag_users = tagUsers ? tagUsers.length : 0;
            var tag_users_select = $('#tag_users_select').val() ? $('#tag_users_select').val().length : 0;
            if (tag_users != tag_users_select) {
                updateUsersForTag($('#tag_search_select').val(), $('#tag_users_select').val());
            }
        }
    });

    $('#tag_description, #tag_script, #tag_as_task').on('change', function() {
        if ($('#tag_search_select').val()) {
            var value = $(this).val();
            var attribute = $(this).attr('name');
            if ($(this).is(':checkbox')) {
                value = $(this).val() == 'on' ? 1 : 0;
            }
            data = {};
            data[attribute] = value;
            updateTag($('#tag_search_select').val(), data);
        }
    });

    // fill users select
    $.get('/tags/getusers', function (response) {
        var result = $.parseJSON(response),
            items = result.data.items;
        if (result.status === 200) {
            $('#tag_users_select').select2({
                data: result.data.items,
                placeholder: "Имя пользователя",
                allowClear: true
            });
        }
    });

    $('#tag_users_select').select2(tagUsersSelectOpts);

    initTagContactsTable();

    $('#tag_create').on('click', function (e) {
        e.preventDefault();
        var startDate = moment().format('YYYY-MM-DD HH:mm:ss'),
            tagNameDate = moment().format('DD/MM/YYYY');

        clearTagData();
        $('#tag_start_date').val(startDate);
        $('#tag_search_select').val(null).trigger("change");
        $('#tag_search_select').select2("destroy").attr('disabled', true).hide();
        $('#tag-add-field').show().find('input').attr('disabled', false);
        //$tagStartDate.val(startDate);
        $('#tag_name').val(tagNameDate + ' ');
        //$this.text('Поиск тегов');
        $(this).hide();
        $('#tag-controls').show();
    });

    $('#tag-controls .ok').on('click', function () {
        createTag($('#tag_name').val());
    });

    $('#tag-controls .remove').on('click', function () {
        $('#tag-controls').hide();
        $('#tag-add-field').hide();
        $('#tag_search_select').select2(tagSelectOpts).attr('disabled', false);
        $('#tag_create').show();
    });

    $('#update_contacts_table').on('click', function () {
        //tagContactsdataTable.draw();
        getContactsForTag($('#tag_search_select').val());
    });

    $('#add_contact_table').on('click', function () {
        if ($('#tag_search_select').val() == '') {
            alert('Выберите тег');
        } else {
            if (!contactTableInitialized) {
                initContactsModalTable($('#tag_search_select').val());
            }

            $('#modalAddContactToTag').modal();
            $('.search-input-text').val('');
            // contactsModaldataTable.search( '' )
            //     .columns().search( '' ).draw();
        }
    });

    $('#add_contact_csv').on('click', function (e) {
        if ($('#tag_search_select').val() == '') {
            alert('Выберите тег');
        } else {
            $('#modalImportCsv').modal();
        }
        e.preventDefault();
    });

    $('#add_contact').on('click', function (e) {
        var tag_id = $('#tag_search_select').val();
        $.post('/tags/add-contacts-by-filter', {
            _csrf: _csrf,
            tag_id: tag_id,
            filters: contactTableFilters
        }, function (response) {
            contactTableInitialized = false;
            getContactsForTag(tag_id);
            //tagContactsdataTable.draw();
        }, 'json');
        $('#modalAddContactToTag').modal('hide');
    });

    var canCall = true;

    $(document).on('click', '#tag_contacts_table .contact-phone', function(e) {
        if (!canCall) {
            return false;
        }
        canCall = false;
        var contactId = $(this).parents('tr').data('id'),
            phone = $(this).data('phone'),
            tag_id = $('#tag_search_select').val();
        openContactForm(contactId);
        initCallNow(phone, tag_id, contactId);
        initRingRound($('#tag_form'));
        setTimeout(function() {
            canCall = true;
        },10000)
    });

    $(document).on('click', '#contacts-table .contact-tags', function(e) {
        var tag_name = $(this).text();
        $('.search-input-text[data-column="tags"]').val(tag_name);
        contactsModaldataTable.columns('tags:name').search(tag_name).draw();
    });
});

function buildDataByTag(data) {
    $('#tag_as_task').prop('checked', data.as_task);
    $('#tag_users_select').val(data.users).trigger("change");
    $('#tag_description').val(data.description);
    $('#tag_script').val(data.script);
    getContactsForTag(data.id);
}

function clearTagData() {
    $('#tag_as_task').prop('checked', 0);
    $('#tag_users_select').val([]).trigger("change");
    $('#tag_description').val('');
    $('#tag_script').val('');
    contactTableInitialized = false;
    tagContactsdataTable.columns(1).search('');
    tagContactsdataTable.draw();
}

function getContactsForTag(tag_id) {
    tagContactsdataTable.columns(1).search(tag_id);
    tagContactsdataTable.draw();
}

function createTag(tag_name) {
    var start_date = $('#tag_start_date').val();
    $.post('/tags/edit', {_csrf: _csrf, name: tag_name, start_date: start_date}, function (response) {
        var newOption = new Option(tag_name, response.data.id, true, true);
        $("#tag_search_select").append(newOption).trigger('change');
        $('#tag-controls').hide();
        $('#tag-add-field').hide();
        $('#tag_search_select').select2(tagSelectOpts).attr('disabled', false);
        $('#tag_create').show();
    }, 'json');
}

function updateUsersForTag(tag_id, users) {
    $.post('/tags/update-users', {_csrf: _csrf, tag_id: tag_id, users: users});
}

function updateTag(tag_id, data) {
    var data = $.extend(data, {_csrf: _csrf, id: tag_id});
    $.post('/tags/edit', data);
}