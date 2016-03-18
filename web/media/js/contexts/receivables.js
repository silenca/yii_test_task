var dataTable;
$(function () {
    var initTable = function () {
        var table = $('#receivables-table');

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
                url: "/receivable/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"targets": 0, "visible": false},
                {"targets": 1, "orderable": false},
                {"targets": 2, "orderable": false,
                    "render": function (data, type, row) {
                        return '<a href="' + data + '" target="_blank">' + data + '</a>';
                    }
                },
                {"targets": 3, "orderable": false},
                {"targets": 4, "orderable": false,
                    "render": function (data, type, row) {
                        if (data.whole_amount > 0) {
                            var payments = '';
                            $.each(data.payments, function(index, payment) {
                                payments += "<li><a href='javascript:void(0)'>" + payment.system_date + "<span>" + payment.amount + "</span></a></li>";
                            });
                            return "<div class='dropdown'>" +
                                    "<a href='javascript:void(0)' data-target='#' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>" + data.whole_amount + "</a>" +
                                    "<ul class='dropdown-menu paid-dropdown pull-left' role='menu'>" +
                                        payments +
                                    "</ul>" +
                                    "</div>";
                        } else {
                            return data.whole_amount;
                        }
                    }
                },
                {"targets": 5, "orderable": false},
                {"targets": 6, "orderable": false}
            ]
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
    if ($('#receivables-table').length) {
        initTable();
    }
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
                "startDate": "01/01/2015",
                "endDate": "31/12/2015",
                "opens": "left",
                "linkedCalendars": true,
                "ranges": {
                    'Сегодня': [moment(), moment()],
                    'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
                    'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                    'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                    'Последний месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, function (start, end, label) {
        dataTable.columns(2).search(start.format('YYYY-MM-DD HH:mm:ss'));
        dataTable.columns(3).search(end.format('YYYY-MM-DD HH:mm:ss'));
        dataTable.draw();
    }
    );
});