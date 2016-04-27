var dataTable,
    dropDownOpened = false;

$(function () {
    // var columns = [
    //     'id',
    //     'int_id',
    //     'surname',
    //     'name',
    //     'middle_name',
    //     'link_with',
    //     'phones',
    //     'emails',
    //     'tags',
    //     'country',
    //     'region',
    //     'area',
    //     'city',
    //     'street',
    //     'house',
    //     'flat',
    //     'delete_button'
    // ];

    var show_columns = columns.filter(function(item) {
        return hide_columns.indexOf(item) === -1;
    });


    var initTable = function () {
        var table = $('#users-table');

        var settings = {
            "sDom": "<'table-responsive't><'row'<p i>>",
            "sPaginationType": "bootstrap",
            "destroy": true,
            "scrollCollapse": true,
            "oLanguage": {
                "sLengthMenu": "_MENU_ ",
                "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
            "iDisplayLength": 10,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                url: "/users/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"orderable": false, "targets": show_columns.indexOf('delete_button')},
                {"orderable": false, "targets": show_columns.indexOf('phones')},
                {"orderable": false, "targets": show_columns.indexOf('emails')},
                {"orderable": false, "targets": show_columns.indexOf('tags')},
                {"orderable": false, "targets": show_columns.indexOf('link_with')},
                {"visible": false, "targets": [show_columns.indexOf('id')]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[show_columns.indexOf('id')]);
                $(row).addClass('open-link');
            }
        };
        $.each(show_columns, function(col_index, col_val) {
            settings.columnDefs.push({ "name": col_val, "targets": col_index })
        });

        $.each(hide_columns, function(i ,val) {
            var index = columns.indexOf(val);
            settings.columnDefs.push({
                "visible": false, "targets" : index
            });
        });
        dataTable = table.DataTable(settings);

        var $searchBoxes = $('input.search-input-text, select.search-input-select');

        $('.search-input-text').on('keyup', function () {   // for text boxes
            // var $currBox = $(this),
            //     currBoxCol = $currBox.data('column'),
            //     currBoxVal = $currBox.val();
            delay(function () {
                $.each($searchBoxes, function (index, val) {
                    var n = $(this).attr('data-column');
                    var v = $(this).val();
                    var strLenDef = 2;
                    if (n == 'city' || n == 'street' || n == 'house' || n == 'flat') {
                        strLenDef = 0;
                    }
                    // var is_int = typeof Number($(this).val()) == 'number';
                    // var strLength = is_int ? 0 : 2;
                    if (v.length > strLenDef || v.length == 0) {
                        dataTable.columns(n+':name').search(v);
                    }
                });
                dataTable.draw();
            }, 2000);
        });

        $('.search-input-select').on('change', function () {   // for select box
            $.each($searchBoxes, function (index, val) {
                var n = $(this).attr('data-column');
                var v = $(this).val();
                dataTable.columns(n+':name').search(v);
            });
            dataTable.draw();
        });
    };


    //events
    if ($('#users-table').length) {
        var $users_table = $('#users-table');

        $('#column_filter').on('click', function (e) {
            e.stopPropagation();
            if ($('#column_filter_modal').is(':visible')) {
                $('#column_filter_modal').hide();
            } else {
                $('#column_filter_modal').show();
            }
        });

        $(document).on('click', function (e) {
            if (!$('#column_filter_modal').find($(e.target)).length && !$('#column_filter_modal').is($(e.target))) {
                $('#column_filter_modal').hide();
            }
        })


/*        $('#column_filter_apply').on('click', function () {
            $('#column_filter_modal').hide();
            var hide_columns = [];
            $('#column_filter_modal input:checkbox:not(:checked)').each(function () {
                hide_columns.push($(this).val());
            })
            $.get('/contacts/hide-columns', {hide_columns: hide_columns}, function() {
                location.reload();
            });
            //dataTable.columnDefs.
        });*/

        //open form
        $users_table.on('click', 'tr', function (e) {
            if (!$(this).parent('thead').length && !$(this).find('.dataTables_empty').length && !hasTarget($(e.target), '.contact_open_disable') && !dropDownOpened) {
                var id = $(this).data('id');
                openUserForm(id);
            }
        });

        //open new form
        $('#open-new-user-form').on('click', function (e) {
            openNewUserForm();
        });

        //remove user
        $users_table.on('click', '.remove', function (e) {
            e.stopPropagation();
            if (confirm('Вы действительно хотите удалить ?')) {
                var $tr = $(this).closest('tr');
                var id = $tr.data('id');
                $.post('/users/delete', {id: id, _csrf: _csrf}, function (response) {
                    var result = $.parseJSON(response);
                    if (result.status === 200) {
                        //initTable();
                        dataTable.row($tr).remove().draw(false);
                        //$tr.empty().remove();
                    }
                });
            }
        });

        $users_table.on('click', 'a', function (e) {
            e.stopPropagation();
        });

        initTable();

        $('table').on('click', '.more', function (e) {
            e.stopPropagation();
            var $cont = $(this).closest('td').find('.additional');
            if ($cont.is(':visible')) {
                $cont.addClass('hide');
            } else {
                $cont.removeClass('hide');
            }
        });

        var toggleDropdown = function (action, $dropDown) {
            var $dropDowns = $('.dropdown'),
                dropMenu = document.getElementsByClassName('link_with-dropdown')[0].cloneNode(true);

            $dropDowns.removeClass("open");
            $dropDowns.find('.link_with-dropdown').remove();
            if (action == 'open') {
                $dropDown.append(dropMenu);
                $dropDown.addClass("open");
            }
            dropDownOpened = $dropDown && $dropDown.hasClass("open") ? true : false;
        };

        $(document).on('click', '.dropdown .dropdown-toggle', function (e) {
            var $dropDownCurr = $(this).parent();

            e.stopPropagation();
            toggleDropdown('open', $dropDownCurr);
            return false;
        });

        $(document).on('click', function (e) {
            var $target = $(e.target);

            if (!hasTarget($target, '.dropdown') && dropDownOpened) {
                toggleDropdown('close');
            }
        });

/*        $(document).on('keyup', '.link_with-dropdown input.search', function(event) {
            var $this = $(this),
                $form = $this.parents('form'),
                search_term = $(this).val(),
                result_items = '';

            if (search_term.length < 2 || search_term.length == 0) {
                $form.find('.result').html('');
            } else if (search_term.length > 2) {
                delay(function () {
                    $.post('contacts/search', {search_term: search_term, _csrf: _csrf}, function (response) {
                        var result = $.parseJSON(response);
                        if (result.status === 200) {
                            $.each(result.data, function(i, el) {
                                result_items += '<tr data-id="' + el.id + '">' +
                                                    '<td>' + el.int_id + '</td>' +
                                                    '<td><a href="javascript:void(0)">' + el.fio + '</a></td>' +
                                                    '<td>' + el.phones + '</td>' +
                                                    '<td>' + el.emails + '</td>' +
                                                '</tr>';
                            });

                            $form.find('.result').html(result_items);
                        } else {
                            $form.find('.result').text('Контакты не найдены');
                        }
                    });
                }, 2000);
            }
        });*/

        $(document).on('click', '.link_with-dropdown .result tr', function() {
            if (!$(this).hasClass('selected')) {
                $('.link_with-dropdown .result tr').removeClass('selected');
            }
            $(this).toggleClass('selected');
        });

/*        $(document).on('click', '.link_with-dropdown .link_btn', function(e) {
            e.preventDefault();
            var $dropdown = $(this).parents('.dropdown'),
                linkedContactId = $(this).parents('tr').data('id'),
                linkToContactId = $dropdown.find('.selected').data('id');

            if (linkToContactId !== undefined) {
                $dropdown.find('.search').val('').attr('disabled', true);
                $dropdown.find('.result').empty();
                $dropdown.find('.link_btn').removeClass('inline').hide();
                $dropdown.find('.loader').addClass('inline');
                $.post('contacts/link-with', {linked_contact_id: linkedContactId, link_to_contact_id: linkToContactId, _csrf: _csrf}, function (response) {
                    var result = $.parseJSON(response);
                    if (result.status === 200) {
                        showNotification('.content', 'Слияние прошло успешно', 'top', 'success', 'bar', 5000);
                        dataTable.draw();
                    } else {
                        $dropdown.find('.loader').removeClass('inline');
                        $dropdown.find('.link_btn').addClass('inline').show();
                        $dropdown.find('.search').attr('disabled', false);
                        $.each(result.errors, function (name, error) {
                            $dropdown.find('.result').html('<div class="error">'+ error +'</div><br>');
                        });
                    }

                });
            }

        });*/
    }
});

function hasTarget($target, elem) {
    return $target.is(elem) || $target.parents(elem).length == 1;
}


