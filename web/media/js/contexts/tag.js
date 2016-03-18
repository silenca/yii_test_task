var dataTable;
var bind_inputs = {};

$(function () {
    var initTable = function () {
        var table = $('#tags-table');

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
                url: "/tags/getdata", // json datasource
                type: "get", // method  , by default get
                error: function () {  // error handling
                    //alert('error data');
                }
            },
            "columnDefs": [
                {
                    "targets": [3, 4],
                    "orderable": false
                },
                {"visible": false, "targets": [0]}
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('data-id', data[0]);
            }
        };
        dataTable = table.DataTable(settings);

        var $searchBoxes = $('input.search-input-text, select.search-input-select');

        $('.search-input-text').on('keyup', function () {   // for text boxes
            delay(function(){
                $.each($searchBoxes, function (index, val) {
                    var i = $(this).attr('data-column');
                    var v = $(this).val();
                    dataTable.columns(i).search(v);
                });
                dataTable.draw();
            }, 1200 );
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

    //evenets on tags page
    if ($('#tags-table').length) {
        var $tags_table = $('#tags-table'),
            $tag_form = $('#modalTagForm');

        //open new form
        $('#open-new-tag-form').on('click', function (e) {
            clearTagForm($tag_form);
            bindLiveChange($tag_form);
            $tag_form.modal({});
        });

        //open form to edit
        $tags_table.on('click', '.edit', function (e) {
            var $tr = $(this).closest('tr');
            var tagId = $tr.data('id'),
                tagName = $tr.find('.tag-name').text(),
                tagDesc =  $tr.find('.tag-description').text();

            $("#tag_id").val(tagId);
            $("#tag_name").val(tagName);
            $("#tag_description").val(tagDesc);
            bindLiveChange($tag_form);
        });

        $tag_form.on('click', '.add_tag-btn', function() {
            if (checkChanges($tag_form)) {
                editTag($tag_form);
            }
        });

        //remove contact
        $tags_table.on('click', '.remove', function (e) {
            e.stopPropagation();
            if (confirm('Вы действительно хотите удалить ?')) {
                var $tr = $(this).closest('tr');
                var id = $tr.data('id');
                $.post('/tags/delete', {id: id, _csrf: _csrf}, function (response) {
                    var result = $.parseJSON(response);
                    if (result.status === 200) {
                        dataTable.row($tr).remove().draw(false);
                    } else {
                        console.log('Fail');
                    }
                });
            }
        });
    }
});

function bindLiveChange($form) {
    $.each($('input[type=text],input[type=email],textarea', $form), function (i, input) {
        var name = $(input).attr('name');
        var val = $(input).val();
        bind_inputs[name] = val;
    });
    bind_inputs['id'] = $('#tag_id').val();
    bind_inputs['is_changed'] = false;
}

function checkChanges($form) {
    $.each($('input[type=text],input[type=email],textarea', $form), function (i, input) {
        var name = $(input).attr('name');
        var value = $(input).val();
        if (bind_inputs[name] !== value) {
            //bind_inputs[name] = value;
            bind_inputs['is_changed'] = true;
        }
    });
    return bind_inputs['is_changed'];
}

function editTag($form) {
    var data = {};
    var $bind_inputs = $form.find('.form-input');
    $.each($bind_inputs, function () {
        data[this.name] = $(this).val();
    });
    data['_csrf'] = _csrf;
    $.post('/tags/edit', data, function (response) {
        $form.find('label.error').remove();
        $form.find('.error').removeClass('error');
        var result = $.parseJSON(response);
        if (result.status == 200) {
            $form.modal('hide');
            showNotification('.page-content-wrapper', 'Тег сохранен.', 'top', 'success', 'bar', 5000);
            dataTable.draw(false);

        } else if (result.status == 415) {
            $.each(result.errors, function (name, errors) {
                addError($form, name, errors);
            });
            //showNotification('body', result.errors, 'top', 'success', 'bar', 5000);
        } else if (result.status == 500) {
            showNotification('.page-content-wrapper', 'Внутренняя ошибка.', 'top', 'success', 'bar', 5000);
        }
        //if (result.status == 415) {
        //    $.each(result.errors, function (name, errors) {
        //        console.log(errors);
        //        //addError($form, name, errors);
        //    });
        //
        //}
    });

}

function addError($form, name, errors) {
    var $field = $form.find('[name="' + name + '"]');
    $.each(errors, function (i, error) {
        $field.addClass('error');
        $field.after("<label class='error'>" + error + "</lable>");
    });

}

function clearTagForm($form) {
    $form.find('#tag_id').val('');
    $form.find('#tag_name').val('');
    $form.find('#tag_description').val('');
    hideNotifications($form);
}
