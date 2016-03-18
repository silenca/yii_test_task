var dataTable;
$(function () {
    var initTable = function () {
        var table = $('#action-table');

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
                url: "/action/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                 { "targets": 0, "orderable": false },
                 { "targets": 1, "orderable": false },
                 { "targets": 2, "orderable": false },
                 { "targets": 3, "orderable": false },
                 { "targets": 4, "orderable": false },
                 { "targets": 5, "orderable": false },
                 { "targets": 6, "orderable": false }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
                $(row).addClass('open-link');
            },
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
    if ($('#action-table').length) { 
        initTable();
    }
});