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
            if ($dataColumn == 'city' || $dataColumn == 'street' || $dataColumn == 'house' || $dataColumn == 'flat') {
                $strLenDef = 0;
            }
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

                //
                // if ($showDelContactsBtn && data._iRecordsDisplay > 0) {
                //     if ($deleteAllContactsBtn.is(':hidden')) {
                //         $deleteAllContactsBtn.fadeIn(200);
                //     }
                //     $({numberValue: $deleteAllContactsBtn.find('span').text()}).animate({numberValue: dataTable.settings()[0].fnRecordsDisplay()}, {
                //         duration: 500,
                //         easing: 'linear',
                //         step: function () {
                //             $deleteAllContactsBtn.find('span').text(Math.ceil(this.numberValue));
                //         },
                //         complete: function () {
                //             $deleteAllContactsBtn.find('span').text(this.numberValue);
                //         }
                //     });
                // } else if ($deleteAllContactsBtn.is(':visible')) {
                //     $deleteAllContactsBtn.fadeOut(150, function () {
                //         $deleteAllContactsBtn.find('span').text(0);
                //     });
                // }
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

        $deleteAllContactsBtn.on('click', function () {
            if (confirm("Вы действительно желаете удалить все найденные контакты (" + dataTable.settings()[0].fnRecordsDisplay() + " шт.)?")) {
                var $res = {};
                $.each($searchBoxes, function (index, val) {
                    var n = $(this).attr('data-column');
                    var v = $(this).val();
                    if (v.length > getSearchStrLenDef(n)) {
                        $res[n] = v;
                    }
                });
                $res["_csrf"] = _csrf;

                $.post(
                    '/contacts/delete-filtered',
                    $res,
                    function (response) {
                        var result = $.parseJSON(response);
                        if (result.status === 200) {
                            dataTable.draw();
                        }
                    }
                );
            }
        });

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
    }

    $('#open-new-sip-channel-from').on('click', function (e) {
        openNewSipChannelForm();
    });
});