$(function () {
    var socket = io.connect(notify_host);
    //var socket = io.connect('http://127.0.0.1:8001');
    socket.emit('join', {notify_id: notify_id});

    socket.on('call_incoming', function (data) {
        var $message_content = $('<div></div>');
        $message_content.append("<div class='alert-header'>Входящий вызов</div>");
        if (data.contact_name) {
            $message_content.append("<div class='alert-link'><a href=/contacts#contact=" + data.id + ">" + data.contact_name + "</a></div>");
            $message_content.append("<div class='alert-details'><span>Номер: </span><span>" + data.phone + "</span></div>");
            var language = data.language === 'rus' ? 'Рус' : 'Укр';
            $message_content.append("<div class='alert-details'><span>Язык: </span><span>" + language + "</span></div>");
        } else {
            $message_content.append("<div class='alert-link'><a href=/contacts#number=" + data.phone + ">" + data.phone + "</a></div>");
        }

        showNotification('body', $message_content.html(), 'bottom-right', 'info', 'circle',30000);
    });
    
    socket.on('new_contract', function (data) {
        var $message_content = $('<div></div>');
        $message_content.append("<div class='alert-header'>Новый договор</div>");
        showNotification('body', $message_content.html(), 'bottom-right', 'info', 'circle',30000);
        var $count_countent = $('ul.menu-items .js-contract_count');
        var contract_count = parseInt($count_countent.text());
        $count_countent.text(++contract_count);
    });
});

/*
 * message: text
 * position: top | bottom | top-left | top-right | bottom-left | bottom-right
 * type: info | warning | success | danger | default
 * style: bar | flip | circle | simple
 */
function showNotification(selector, message, position, type, style, timeout) {
    if (!timeout) timeout = 0;
    $(selector).pgNotification({
        style: style,
        message: message,
        position: position,
        timeout: timeout,
        type: type
    }).show();
}

function hideNotifications(selector) {
    selector.find('.pgn').remove();
}