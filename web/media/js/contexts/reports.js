var dataTable;
var incoming = 0;
var outgoing = 0;
var leads = 0;
var visit = 0;
var show = 0;
var deal = 0;

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
                {"targets": 4, "orderable": false},
                {"targets": 5, "orderable": false},
                {"targets": 6, "orderable": false},
            ],
            "createdRow": function (row, data, index) {
                incoming += parseInt(data[1]);
                outgoing += parseInt(data[2]);
                leads += parseInt(data[3]);
                visit += parseInt(data[4]);
                show += parseInt(data[5]);
                deal += parseInt(data[6]);
            },
            "drawCallback": function () {
                var content_width = $('.container-fluid').css('width');
                var $incoming = $('.report-visual .incoming').animate({width: '800px'}, 1000);
                $incoming.find('p').text("Входящие " + outgoing);
                var factor = 800 / outgoing;
                if (leads != 0) {
                    var leads_width = factor * leads;
                } else {
                    leads_width = 0;
                }
                if (leads_width > parseInt(content_width)) {
                    leads_width = '100%';
                } else if (leads_width < 85) {
                    leads_width = 85 + "px";
                } else {
                    leads_width = leads_width + "px";
                }
                var $leads = $('.report-visual .leads').animate({
                    width: leads_width
                }, 1000);
                $leads.find('p').text("Лиды " + leads);
                if (visit != 0) {
                    var visit_width = factor * visit;
                } else {
                    visit_width = 0;
                }
                if (visit_width > parseInt(content_width)) {
                    visit_width = '100%';
                } else if (visit_width < 85) {
                    visit_width = 85 + "px";
                } else {
                    visit_width = visit_width + "px";
                }
                var $leads = $('.report-visual .visit').animate({width: visit_width}, 1000);
                $leads.find('p').text("Визит " + visit);
                if (show != 0) {
                    var show_width = factor * show;
                } else {
                    show_width = 0;
                }
                if (show_width > parseInt(content_width)) {
                    show_width = '100%';
                } else if (show_width < 85) {
                    show_width = 85 + "px";
                } else {
                    show_width = show_width + "px";
                }
                var $show = $('.report-visual .show').animate({width: show_width}, 1000);
                $show.find('p').text("Показ " + show);
                if (deal != 0) {
                    var deal_width = factor * deal;
                } else {
                    deal_width = 0;
                }
                if (deal_width > parseInt(content_width)) {
                    deal_width = '100%';
                } else if (deal_width < 85) {
                    deal_width = 85 + "px";
                } else {
                    deal_width = deal_width + "px";
                }
                var $deal = $('.report-visual .deal').animate({width: deal_width}, 1000);
                $deal.find('p').text("Сделки " + deal);
                $('.report-visual').show();
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
        incoming = outgoing = leads = visit = show = deal = 0;
    };

    if ($('#reports-table').length) {
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