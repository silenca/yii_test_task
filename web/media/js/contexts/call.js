$(function(){
    var log = function(m){ console.log('[C]', m); };

    var columns = _.reduce($('.dt_column'), function(cols, el){
        var $el = $(el);
        cols[$el.data('name')] = $el.data();
        return cols;
    }, {});

    var handlers = {
        type: {
            createdCell: function(td, cellData, rowData, row, col) {
                if (cellData == 'Входящий - сбой' || cellData == "Исходящий - сбой") {
                    $(td).closest('tr').addClass('failure');
                }
                if (cellData == 'Пропущенный' && rowData[1] == 1) {
                    $(td).closest('tr').addClass('missed');
                }
            }
        }
    };

    var columnDefs = _.reduce(columns, function(defs, column, name){
        defs.push(_.extend({
            targets: defs.length,
            bSortable: false,
            bVisible: column.hasOwnProperty('visible')?column.visible:true
        }, handlers[name] || {}));
        return defs;
    }, []);

    (function(){
        var dataTable = $('#call-table').DataTable({
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
                url: "/call/getdata",
                error: function () {

                }
            },
            "columnDefs": columnDefs,
            "createdRow": function (row, data, index) {
                $(row).attr('id', data[0]);
                $(row).data('id', data[0]);
            }
        });

        var $searchBoxes = $('input.search-input-text, select.search-input-select');
        var doSearch = function(){
            _.each($searchBoxes, function(el){
                var i = $(el).attr('data-column');
                var v = $(el).val();
                dataTable.columns(i).search(v);
            });
            dataTable.draw();
        };

        $('.search-input-text').on('keyup', function(){
            _.delay(doSearch, 2000);
        });

        $('select.search-input-select').on('change', doSearch);
    })();

    $(document).on('click', '#call-table .contact', function(e) {
        var id = $(this).data('id'),
            phone = $(this).data('phone');
        if(id !== "") {
            openContactForm(id);
        } else {
            openNewContactFormWithPhone(phone);
        }
    });
});