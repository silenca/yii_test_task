var currentPage = "reports";
var dataTable;
// var incoming = 0;
// var outgoing = 0;
// var leads = 0;
var tag_filters = [];
var start_date_filter = null;
var end_date_filter = null;
var user_filter = null;
var attraction_channel = null;

var date = new Date();
var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

var firstDayFormatted = (firstDay.getDate()) + '/' + (firstDay.getMonth() + 1) + '/' + firstDay.getFullYear();
var lastDayFormatted = (lastDay.getDate()) + '/' + (lastDay.getMonth() + 1) + '/' + lastDay.getFullYear();

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
                /*{
                    "targets": 3,
                    "orderable": false,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        var tags = '';
                        cellData.forEach(function (val) {
                            var tagClass = 'tag label';
                            if (val['is_deleted'] == 1) {
                                tagClass += ' label-danger';
                            }
                            tags += '<div class="' + tagClass + '" data-id="' + val['tag_id'] + '">' + val['name'] + '</div>';
                        });
                        $(td).html("<div class='tags_block clearfix'>" + tags + "</div>");
                    }
                },*/
                {"targets": 4, "orderable": false},
                {"targets": 5, "orderable": false},
            ],
            "createdRow": function (row, data, index) {

                // incoming += parseInt(data[1]);
                // outgoing += parseInt(data[2]);
                // leads += parseInt(data[3]);
            },
            "drawCallback": function () {
                tag_filters.forEach(function (val) {
                    $('.tag[data-id=' + val + ']').addClass('label-success');
                });

            }
        };
        dataTable = table.DataTable(settings);

        $('.search-input-select').on('change', function () {   // for select box
            user_filter = $(this).val();
            user_filter ? dataTable.columns(0).search(user_filter) : null;
            start_date_filter ? dataTable.columns(1).search(start_date_filter) : null;
            end_date_filter ? dataTable.columns(2).search(end_date_filter) : null;
            tag_filters ? dataTable.columns(3).search(tag_filters) : null;
			attraction_channel ? dataTable.columns(4).search(attraction_channel) : null;
            dataTable.draw();
        });

        $('.search-input-select-two').on('change', function () {   // for select box
			attraction_channel = $(this).val();
            user_filter ? dataTable.columns(0).search(user_filter) : null;
            start_date_filter ? dataTable.columns(1).search(start_date_filter) : null;
            end_date_filter ? dataTable.columns(2).search(end_date_filter) : null;
            tag_filters ? dataTable.columns(3).search(tag_filters) : null;
			attraction_channel ? dataTable.columns(4).search(attraction_channel) : null;
            dataTable.draw();
        });
    };

    var resetVars = function () {
        //incoming = outgoing = leads = 0;
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
            start_date_filter = start.format('YYYY-MM-DD HH:mm:ss');
            end_date_filter = end.format('YYYY-MM-DD HH:mm:ss');
            user_filter ? dataTable.columns(0).search(user_filter) : null;
            start_date_filter ? dataTable.columns(1).search(start_date_filter) : null;
            end_date_filter ? dataTable.columns(2).search(end_date_filter) : null;
            tag_filters ? dataTable.columns(3).search(tag_filters) : null;
			attraction_channel ? dataTable.columns(4).search(attraction_channel) : null;
            dataTable.draw();
        }
    );

    $('table').on('click', '.tag', function () {
        //$(this).toggleClass('label-success');
        if (tag_filters.indexOf($(this).data('id')) == -1) {
            tag_filters.push($(this).data('id'));
        } else {
            tag_filters.splice(tag_filters.indexOf($(this).data('id')), 1);
        }

        user_filter ? dataTable.columns(0).search(user_filter) : null;
        start_date_filter ? dataTable.columns(1).search(start_date_filter) : null;
        end_date_filter ? dataTable.columns(2).search(end_date_filter) : null;
        tag_filters ? dataTable.columns(3).search(tag_filters) : null;
		attraction_channel ? dataTable.columns(4).search(attraction_channel) : null;
        dataTable.draw();
    });
});