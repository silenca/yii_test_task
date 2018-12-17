var currentPage = "call";
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
            "bPaginate" : true,
            "scrollCollapse": true,
            "oLanguage": {
                "sLengthMenu": "_MENU_ ",
                "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
            },
            "iDisplayLength": 20,
            "processing": true,
            "serverSide": false,
            "order": [],
            "ajax": {
                url: "/call/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columns" : [
                { "data": "date" },
                { "data": "time" },
                { "data": "type" },
                { "data": "manager" },
                { "data": "contact", "render": function (data){
                        if(typeof(data) === 'object'){
                            return data.name === "" ? '<a class="contact" data-contact_id="" data-phone="'+data.first_phone+'" href="javascript:void(0)">'+data.first_phone+'</a>' : '<a class="contact" data-contact_id="'+data.id+'" data-phone="'+data.first_phone+'" href="javascript:void(0)">'+data.name+'</a>';
                        }
                        return '<a class="contact" data-contact_id="" data-phone="'+data+'" href="javascript:void(0)">'+data+'</a>';
                    }
                },
                { "data": "record", "render": function (data) {
                        return '<audio controls="" src="https://dopomogaplus.silencatech.com/var/spool/asterisk/monitor/'+data+'.mp3" type="audio/mpeg"></audio>'
                    }
                }
            ],
            // "columnDefs": columnDefs,
            "createdRow": function (row, data, index) {
                $(row).attr('id', data[0]);
            }
            //"bSort": [[0, 1, 4]]
        };
        dataTable = table.DataTable(settings);

        var $searchBoxes = $('input.search-input-text, select.search-input-select');

        $('.search-input-text').on('keyup', function () {   // for text boxes
            delay(function () {
                $.each($searchBoxes, function (index, val) {
                    var i = $(this).attr('data-column');
                    var v = $(this).val();
                    if (v.length >= 0) {
                        dataTable.columns(i).search(v);
                    }
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
    initTable();

    $('table').on('click', '.contact', function () {
        var id = $(this).closest('tr').attr('id');
        viewNotify(id);
    });

    $(document).on('click', '#call-table .contact', function(e) {
        var contactId = $(this).data('contact_id'),
            phone = $(this).data('phone');
        if (contactId !== "") {
            openContactForm(contactId);
        } else {
            openNewContactFormWithPhone(phone);
            initCallNow(phone, contactId);
        }
    });

    $(document).on('click', '#call-table .contact-tags', function(e) {
        var tag_name = $(this).text();
        $('.search-input-text.data-column-tags').val(tag_name);
        dataTable.columns(6).search(tag_name).draw();
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