var dataTable;
$(function () {
    var initTable = function () {
        var table = $('#manager_notify-table');

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
                url: "/managernotify/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"targets": 0, "orderable": false, "visible": false},
                {"targets": 1, "orderable": false},
                {"targets": 2, "orderable": false,
                    "render": function (data, type, row) {
                        if (data.schedule_date) {
                            return "<div class='dropdown'>" +
                                    "<a href='javascript:void(0)' data-target='#' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>" + data.type + "</a>" +
                                    "<ul class='dropdown-menu type-dropdown pull-left' role='menu'>" +
                                        "<li><a href='javascript:void(0)'>" + data.schedule_date + "</a></li>" +
                                    "</ul>" +
                                    "</div>";
                        } else {
                            return data.type;
                        }
                    }
                },
                {"targets": 3, "orderable": false},
                {"targets": 4, "orderable": false},
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', data[0]);
                if (data[6] == 0) {
                    $(row).addClass('unread');
                }
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
    if ($('#manager_notify-table').length) {
        initTable();

        $('table').on('click', '.contact', function () {
            var id = $(this).closest('tr').attr('id');
            viewNotify(id);
        });
    }
});

function viewNotify(id) {
    $.post('/managernotify/view', {id: id, _csrf: _csrf}, function () {
        $('table').find('#'+id).removeClass('unread');
    });
}