/**
 *
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

var currentPage = "sip-channel";
var dataTable,
    dropDownOpened = false;

$(function () {
    var show_columns = columns.filter(function (item) {
        return hide_columns.indexOf(item) === -1;
    });

    var initTable = function () {
        var table = $('#sip-channel-table');
        var $searchBoxes = $('input.search-input-text, select.search-input-select');
        var $deleteAllContactsBtn = $('#delete_all_filtered_contacts');

        function getSearchStrLenDef($dataColumn) {
            var $strLenDef = 2;
            return $strLenDef;
        }

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
                url: "/sip-channel/get-data", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"visible": false, "targets": [show_columns.indexOf('id')]},
                {"orderable": false, "targets": []}
            ],
            'fnDrawCallback': function (data) {
                // Show hide delete filtered contacts
                var $showDelContactsBtn = false;
                $.each($searchBoxes, function (index, val) {
                    if (!$showDelContactsBtn && $(this).val().length > getSearchStrLenDef($(this).attr('data-column'))) {
                        $showDelContactsBtn = true;
                    }
                });
            },
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[show_columns.indexOf('id')]);
                $(row).addClass('open-link');
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
            settings.columnDefs[0].targets.push(index);
        });

        dataTable = table.DataTable(settings);

        $('.search-input-text').on('keyup', function () { // for text boxes
            delay(function () {
                $.each($searchBoxes, function (index, val) {
                    var n = $(this).attr('data-column');
                    var v = $(this).val();
                    if (!(v.length > getSearchStrLenDef(n) || v.length == 0)) {
                        v = '';
                    }
                    dataTable.columns(n + ':name').search(v);
                });
                dataTable.draw();
            }, 2000);
        });

        $('.search-input-select').on('change', function () {   // for select box
            $.each($searchBoxes, function (index, val) {
                var n = $(this).attr('data-column');
                var v = $(this).val();
                dataTable.columns(n + ':name').search(v);
            });
            dataTable.draw();
        });
    };

    if ($('#sip-channel-table').length) {
        initTable();

        $('#column_filter').on('click', function (e) {
            e.stopPropagation();
            var modal = $('#column_filter_modal');
            if (modal.is(':visible')) {
                modal.hide();
            } else {
                modal.show();
            }
        });

        $(document).on('click', function (e) {
            var modal = $('#column_filter_modal');
            if (!modal.find($(e.target)).length && !modal.is($(e.target))) {
                modal.hide();
            }
        });

        $('#column_filter_apply').on('click', function () {
            var modal = $('#column_filter_modal');
            modal.hide();
            var hide_columns = [];
            modal.find('input:checkbox:not(:checked)').each(function () {
                hide_columns.push($(this).val());
            });
            $.get('/sip-channel/hide-columns', {hide_columns: hide_columns}, function () {
                location.reload();
            });
            //dataTable.columnDefs.
        });

        var $sip_channel_table = $('#sip-channel-table');

        $sip_channel_table.on('click', 'tr', function (e) {
            if (!$(this).parent('thead').length && !$(this).find('.dataTables_empty').length && !hasTarget($(e.target), '.user_open_disable') && !dropDownOpened) {
                var id = $(this).data('id');
                openSipChannelForm(id);
            }
        });

        $sip_channel_table.on('click', '.remove', function (e) {
            e.stopPropagation();
            if (confirm('Вы действительно хотите удалить ?')) {
                var $tr = $(this).closest('tr');
                var id = $tr.data('id');
                $.post('/sip-channel/delete', {id: id, _csrf: _csrf}, function (response) {
                    var result = $.parseJSON(response);
                    if (result.status === 200) {
                        //initTable();
                        dataTable.row($tr).remove().draw(false);
                        //$tr.empty().remove();
                    }
                });
            }
        });
    }

    $('#open-new-sip-channel-from').on('click', function (e) {
        openNewSipChannelForm();
    });
});

function hasTarget($target, elem) {
    return $target.is(elem) || $target.parents(elem).length == 1;
}
