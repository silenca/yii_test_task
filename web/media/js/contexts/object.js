var dataTable;
$(function () {
    var initTable = function () {
        var table = $('#object-table');

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
                url: "/object/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                { "width": "18%" },
                { "width": "9%" },
                { "width": "8%" },
                { "width": "15%" },
                { "width": "11%" },
                { "width": "9%" },
                { "width": "15%" },
                { "width": "15%" }
            ],
            "columnDefs": [
                {"visible": false, "targets": [0, 1]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
                $(row).attr('data-link', data[1]);
                $(row).addClass('open-link');
            }
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
    if ($('#object-table').length) { 
        var $object_table = $('#object-table');
        
        //remove object
        $object_table.on('click','.remove', function(e) {
            e.stopPropagation();
            if (confirm('Вы действительно хотите удалить ?')) {
                var $tr = $(this).closest('tr');
                var id = $tr.data('id');
                $.post('/object/delete', {id: id, _csrf: _csrf}, function (response) {
                    var result = $.parseJSON(response);
                    if (result.status === 200) {
                        dataTable.row($tr).remove().draw(false);
                    }
                });
            }
        });
        initTable();
    }

    var commentTextOld;

    $('#object-table_wrapper').on('click', '#object-table tr .object-btn', function(e) {
        var commentText = $(this).parent().find('.object-comment').text(),
            objectId = $(this).parents('tr').data('id');
        commentTextOld = commentText;

        $("#object-id").val(objectId);
        $("#object_comment").val(commentText);
    });

    $('#modalEditComment').on('click', '.add_comment-btn', function(e) {
        e.preventDefault();
        var commentText = $("#object_comment").val(),
            objectId = $("#object-id").val();

        if (commentTextOld !== commentText) {
            $.post('/object/edit-comment', {'object-id': objectId, 'object-comment': commentText, '_csrf': _csrf}, function (response) {
                var result = $.parseJSON(response);
                if (result.status === 200) {
                    $('#modalEditComment').modal('hide');
                    dataTable.draw(false);
                }
            });
        }
    });

    $('#object-table_wrapper').on('click', '#object-table tr', function(e) {
        var objLink = $(this).data('link');
        if (!$(e.target).hasClass('object-btn') && $(e.target).parents('thead').length == 0) {
            window.open(objLink, '_blank');
        }
    });
});