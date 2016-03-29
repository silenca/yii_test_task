$(function () {
    $('#choose-file').on('click', function () {
        $('#csv-file').trigger('click');
    });

    $('#csv-file').on('change', function () {
        var file_name = $(this).val().replace(/.*[\/\\]/, '');
        if (!file_name.match('\.csv$')) {
            alert('Выберите CSV файл');
            return false;
        }
        $('#file-name').text(file_name);
    });

    $('form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        if ($('#csv-file').val() == '') {
            alert('Выберите CSV файл');
            return false;
        }
        var data = new FormData();
        data.append('_csrf', _csrf);
        data.append('csv_file', $('#csv-file')[0].files[0]);
        //var data = $(this).serialize();
        $form.find('.error').empty();
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (result) {
                if (result.errors) {
                    $.each(result.errors, function (i, val) {
                        $form.find('.result').append("<div>" + val + "</div>");
                    })
                }
                if (result.status !== 500) {
                    $form.find('.result').append("<div>Импортировано " + result.data.imported + " из " + result.data.count + "</div>");
                    if (result.status != 200) {
                        $form.find('.result').append("<a href='" + result.data.report_file + "' target='_blank'>Отчет об ошибках</a>");
                    }
                }

            }
        });
    })


});