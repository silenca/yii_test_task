var dataTable,
    dropDownOpened = false;

$(function () {
    var columns = [
        'id',
        'int_id',
        'surname',
        'name',
        'middle_name',
        'link_with',
        'phones',
        'emails',
        'tags',
        'country',
        'region',
        'area',
        'city',
        'street',
        'house',
        'flat',
        'delete_button'
    ];

    var show_columns = columns.filter(function(item) {
        return hide_columns.indexOf(item) === -1;
    });


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
                {"orderable": false, "targets": show_columns.indexOf('delete_button')},
                {"orderable": false, "targets": show_columns.indexOf('phones')},
                {"orderable": false, "targets": show_columns.indexOf('emails')},
                {"orderable": false, "targets": show_columns.indexOf('tags')},
                {"orderable": false, "targets": show_columns.indexOf('link_with')},
                {"visible": false, "targets": [0]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
                $(row).addClass('open-link');
            }
        };
        $.each(hide_columns, function(i ,val) {
            var index = columns.indexOf(val);
            settings.columnDefs.push({
                "visible": false, "targets" : index
            });
        });
        dataTable = table.DataTable(settings);

        var $searchBoxes = $('input.search-input-text, select.search-input-select');

        $('.search-input-text').on('keyup', function () {   // for text boxes
            delay(function () {
                $.each($searchBoxes, function (index, val) {
                    var i = $(this).attr('data-column');
                    var v = $(this).val();
                    if (v.length > 2 || v.length == 0) dataTable.columns(i).search(v);
                });
                dataTable.draw();
            }, 2000);
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


        $('#column_filter_apply').on('click', function () {
            $('#column_filter_modal').hide();
            var hide_columns = [];
            $('#column_filter_modal input:checkbox:not(:checked)').each(function () {
                hide_columns.push($(this).val());
            })
            $.get('/contacts/hide-columns', {hide_columns: hide_columns}, function() {
                location.reload();
            });
            //dataTable.columnDefs.
        });

        //open form
        $contact_table.on('click', 'tr', function (e) {
            if (!$(this).parent('thead').length && !$(this).find('.dataTables_empty').length && !hasTarget($(e.target), '.contact_open_disable') && !dropDownOpened) {
                var id = $(this).data('id');
                openContactForm(id);
            }
        });

        //open new form
        $('#open-new-contact-from').on('click', function (e) {
            openNewContactForm();
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

        $contact_table.on('click', 'a', function (e) {
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

        $('.link_with-dropdown input.search').on('keyup', function(event) {
            var $this = $(this),
                $form = $this.parents('form'),
                search_term = $(this).val();

            $.post($form.attr('action'), {search_term: search_term, _csrf: _csrf}, function (response) {
                var result = $.parseJSON(response);
                if (result.status === 200) {
                    
                }
            });

            var search_result = search_obj.search(search_term);

            var result_list = '',
                result_items = '';

            $.each(search_result, function() {
                var $product = $(this);

                result_items += '<li><a href="product.php?product=' + $product[0].code + '">' + $product[0].category + ' ' + $product[0].trademark + ' ' + $product[0].model + '</a></li>';
            });

            result_list = '<ul id="search_result_list">' + result_items + '</ul>';

            if (search_result.length) {
                $('#search_result').show();
                $('#search_result').html(result_list);
            } else {
                $('#search_result').hide();
                $('#search_result').html('');
            }

        });
    }
});

function hasTarget($target, elem) {
    return $target.is(elem) || $target.parents(elem).length == 1;
}


