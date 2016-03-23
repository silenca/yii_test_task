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
                {
                    "targets": [0, 1, 2, 3, 4, 5, 6],
                    //"data": null,
                    "orderable": false,
                    //"defaultContent": '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>'
                },
                {"visible": false, "targets": [0]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
                $(row).attr('data-schedule_date', data[4]);
                if (data[7] == 0) {
                    $(row).addClass('unread');
                }
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

    $('#action-table').on('click', '.open_contact' , function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr'),
            contact_id = $(this).data('id'),
            action_id = $row.data('id'),
            action_date = $row.data('schedule_date');
        openContactForm(contact_id);
        if ($row.hasClass('unread')) {
            viewAction(action_id, action_date);
        }
    });
});

function viewAction(id, date) {
    $.post('/action/view', {id: id, date: date, _csrf: _csrf}, function (response) {
        var result = $.parseJSON(response);
        if (result.status === 200) {
            $('#action-table').find('tr[data-id='+id+']').removeClass('unread');
        }
    });
}