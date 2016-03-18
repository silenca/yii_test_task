var dataTable;
$(function () {
    var columns_count = $('#call-table thead th').length;
    var columnDefs = [];
    for (var i = columns_count - 1; i >= 4; i--) {
        columnDefs.push({"targets": i, "orderable": false});
    }
    columnDefs.push(
            {
                "targets": 4,
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (cellData == 'Входящий - сбой' || cellData == "Исходящий - сбой") {
                        $(td).closest('tr').addClass('failure');
                    }
                    if (cellData == 'Пропущенный' && rowData[1] == 1) {
                        $(td).closest('tr').addClass('missed');
                    }
                }
            },
    {
        "visible": false, "targets": [0]
    },
    {
        "visible": false, "targets": [1]
    }
    );


    var initTable = function () {
        var table = $('#call-table');

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
                url: "/call/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": columnDefs,
            "createdRow": function (row, data, index) {
                $(row).attr('id', data[0]);
            }
            //"bSort": [[0, 1, 4]]
        };
        dataTable = table.DataTable(settings);

        $('.search-input-text').on('keyup', function () {   // for text boxes
            var inputs = $('.search-input-text');
            $.each(inputs, function (index, val) {
                var i = $(this).attr('data-column');
                var v = $(this).val();
                dataTable.columns(i).search(v);
            });
            dataTable.draw();
        });

        $('.search-input-select').on('change', function () {   // for select box
            var selects = $('.search-input-select');
            $.each(selects, function (index, val) {
                var i = $(this).attr('data-column');
                var v = $(this).val();
                dataTable.columns(i).search(v);
            });
            dataTable.draw();
        });
    };
    initTable();

    $('table').on('click', '.contact', function () {
        var id = $(this).closest('tr').attr('id');
        viewNotify(id);
    });
});


function viewNotify(id) {
    $.post('/call/view', {id: id, _csrf: _csrf}, function (response) {
        var result = $.parseJSON(response);
        if (result.status === 200) {
            var $tr = $('table').find('tr#' + id);
            dataTable.row($tr).remove().draw(false);
        }
    });
}