var dataTable;
var solution_form;

$(function () {
    var initTable = function () {
        var table = $('#contract-table');

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
                url: "/contracts/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {"targets": 0, "orderable": false, "visible": false},
                {"targets": 1, "orderable": false},
                {"targets": 2, "orderable": false},
                {"targets": 3, "orderable": false},
                {"targets": 4, "orderable": false},
                {"targets": 5, "orderable": false},
                {"targets": 6, "orderable": false},
                {
                    "targets": 7,
                    "render": function (data, type, row) {
                        if (data === "none") {
                            return '<div class="solution"><button class="btn btn-complete">Нет решения</button></div>';
                        } 
                        return data;
                    },
                    "orderable": false,
                    //"defaultContent": '<div class="solution"><button class="btn btn-complete">Нет решения</button></div>'
                }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
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
    if ($('#contract-table').length) {
        initTable();

        $('#contract-table').on('click', '.solution button', function (e) {
            e.stopPropagation();
            var $form = $('.solution_form');
            var $tr = $(this).closest('tr');
            var id = $tr.data('id');
            $form.css('top', $(this).offset().top);
            $form.css('left', $(this).offset().left - 105);
            var options = [];
            options['0'] = 'Ничего не выбрано';
            options['approved'] = 'Одобрено';
            options['revision'] = 'Доработать';
            options['rejected'] = 'Отклонено';
            solution_form = new SolutionForm({
                wrapper: $form,
                options: options,
                close: true,
                onSolution: function (solution_type) {
                    $tr.find('.solution').html("<b>" + options[solution_type] + "</b>");
                    var contractsCount = parseInt($('.js-contract_count').text());
                    if ((contractsCount - 1) !== 0) {
                        $('.js-contract_count').text(contractsCount - 1);
                    } else {
                        $('.js-contract_count').remove();
                    }
                }
            });
            var $solution_form_view = solution_form.init();
            solution_form.setId(id);
            var el = $solution_form_view.find('select').get(0);
            $(el).wrap('<div class="cs-wrapper"></div>'), new SelectFx(el);
        });

    }
});
