var dataTable;
var incoming = 0;
var outgoing = 0;
var leads = 0;

$(function () {
    var initTable = function () {
        var table = $('#reports-table');

        var settings = {
            "sDom": "<'table-responsive't><'row'<p i>>",
            "sPaginationType": "bootstrap",
            "destroy": true,
            "scrollCollapse": true,
//            "oLanguage": {
//                "sLengthMenu": "_MENU_ ",
//                "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
//            },
            "info": false,
            "iDisplayLength": 20,
            "paging": false,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                url: "/reports/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"targets": 0, "orderable": false},
                {"targets": 1, "orderable": false},
                {"targets": 2, "orderable": false},
                {"targets": 3, "orderable": false},
            ],
            "createdRow": function (row, data, index) {
                incoming += parseInt(data[1]);
                outgoing += parseInt(data[2]);
                leads += parseInt(data[3]);
            },
            "drawCallback": function () {
                resetVars();
            }
        };
        dataTable = table.DataTable(settings, function () {

        });

        $('.search-input-select').on('change', function () {   // for select box
            console.log("search-input-select changed");
            var selects = $('.search-input-select');
            $.each(selects, function (index, val) {
                var i = $(this).attr('data-column');
                var v = $(this).val();
                dataTable.columns(i).search(v);
            });
            dataTable.draw();
        });
    };

    var resetVars = function () {
        incoming = outgoing = leads = 0;
    };

    if ($('#reports-table').length) {
        initTable();
    }

    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

    var firstDayFormatted = (firstDay.getDate()) + '/' + (firstDay.getMonth() + 1) + '/' + firstDay.getFullYear();
    var lastDayFormatted = (lastDay.getDate()) + '/' + (lastDay.getMonth() + 1) + '/' + lastDay.getFullYear();

    $('input[name="daterange"]').daterangepicker(
            {
                "locale": {
                    "format": "DD.MM.YYYY",
                    "separator": " - ",
                    "applyLabel": "Применить",
                    "cancelLabel": "Отмена",
                    "fromLabel": "From",
                    "toLabel": "To",
                    "customRangeLabel": "Календарь",
                    "daysOfWeek": [
                        "Вс",
                        "Пн",
                        "Вт",
                        "Ср",
                        "Чт",
                        "Пт",
                        "Сб"
                    ],
                    "monthNames": [
                        "Январь",
                        "Февраль",
                        "Март",
                        "Апрель",
                        "Май",
                        "Июнь",
                        "Июль",
                        "Август",
                        "Сентябрь",
                        "Октябрь",
                        "Ноябрь",
                        "Декабрь"
                    ],
                    "firstDay": 1
                },
                "startDate": firstDayFormatted,
                "endDate": lastDayFormatted,
                "opens": "left",
                "linkedCalendars": true,
                "ranges": {
                    'Сегодня': [moment(), moment()],
                    'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
                    'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                    'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                    'Последний месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'За всё время': ['01.05.15', moment()]
                }
            }, function (start, end, label) {
        dataTable.columns(1).search(start.format('YYYY-MM-DD HH:mm:ss'));
        dataTable.columns(2).search(end.format('YYYY-MM-DD HH:mm:ss'));
        dataTable.draw();
    }
    );
});