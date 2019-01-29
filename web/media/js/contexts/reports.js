var currentPage = "reports";

var date = new Date();
var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

var firstDayFormatted = (firstDay.getDate()) + '/' + (firstDay.getMonth() + 1) + '/' + firstDay.getFullYear();
var lastDayFormatted = (lastDay.getDate()) + '/' + (lastDay.getMonth() + 1) + '/' + lastDay.getFullYear();

$(function () {
    var initTable = function(wr) {
        var settings = {
            "sDom": "<'table-responsive't><'row'<p i>>",
            "sPaginationType": "bootstrap",
            "destroy": true,
            "scrollCollapse": true,
            "info": false,
            "iDisplayLength": 20,
            "paging": false,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                url: "/reports/getdata",
                type: "get",
                error: function(){}
            },
            "columnDefs": [
                {"targets": 0, "orderable": false},
                {"targets": 1, "orderable": false},
                {"targets": 2, "orderable": false},
                {"targets": 3, "orderable": false},
                {"targets": 4, "orderable": false},
                {"targets": 5, "orderable": false},
            ],
            "createdRow": function(row, data, index){},
            "drawCallback": function(){}
        };
        return $(wr).DataTable(settings);
    };

    if(!$('#reports-table').length) {
        return;
    }

    var dataTable = initTable('#reports-table');

    var activeFilters = {};
    var filtersMap = {user: 0, start: 1, end: 2, tag: 3, attrChannel: 4};
    var updateTable = _.throttle(function(){
        _.each(activeFilters, function(value, name){
            if(value) {
                dataTable.columns(filtersMap[name]).search(value);
            }
        });
        dataTable.draw();
    }, 200, { leading: false });

    $('select.search-input-select').on('change', function(){
        activeFilters.user = $(this).val();
        updateTable();
    });

    $('select.search-input-select-two').on('change', function(){
        activeFilters.attrChannel = $(this).val();
        updateTable();
    });

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
            activeFilters.start = start.format('YYYY-MM-DD HH:mm:ss');
            activeFilters.end = end.format('YYYY-MM-DD HH:mm:ss');
            updateTable();
        }
    );
});