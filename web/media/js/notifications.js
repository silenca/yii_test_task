 $(function(){
  var socket = io.connect('ws://dopomogaplus.silencatech.com:8005', {secure:false});
    console.log(socket);
    socket.emit('join', {notify_id: notify_id, role_id: role_id}, function(e){
        console.log('joined', e);
    });

    function acceptCall(cal_id) {
        $.post('/contacts/accept-call', {call_id:cal_id,_csrf:_csrf}, function (response) {});
    }

    socket.on('call_incoming', function (data) {
        console.log("Incoming call:");
        console.log(data);
        var $message_content = $('<div></div>');
        $message_content.append("<div class='alert-header'>Входящий вызов</div>");
        if (data.id) {
            $message_content.append("<div class='alert-link'><span>" + data.contact_name + "</span></div>");
            $message_content.append("<div class='alert-details'><span>Номер: </span><a href='javascript:void(0)' data-contact-id='" +
                data.id +
                "' class='notification-open-contact' data-call-id='"+data.call_id+"'>"
                + data.phone + "</a></div>");
        } else {
            $message_content.append("<div class='alert-link'><a href='javascript:void(0)' class='notification-open-new-contact' data-attraction-channel-id='" +
                (data.attraction_channel_id!==undefined?data.attraction_channel_id:'') +
                "' data-call-id='"+data.call_id+"'>" + data.phone + "</a></div>");
        }

        showNotification('body', $message_content.html(), 'bottom-right', 'info', 'circle',0);

        $('.notification-open-contact').off('click').on('click', function (e) {
            var contactId = $(this).data('contact-id'),
                phone = $(this).text();
            openContactForm(contactId);
            // initCallNow(phone, null, contactId);
            acceptCall($(this).data('call-id'));
        });

        $('.notification-open-new-contact').off('click').on('click', function (e) {
            openNewContactForm($(this).text(),$(this).data('attraction-channel-id'));
            acceptCall($(this).data('call-id'));
        });
    });

    socket.on('close_call', function (data) {
        if(data.call_id != undefined) {
            var link = $('.notification-open-new-contact[data-call-id="'+data.call_id+'"],.notification-open-contact[data-call-id="'+data.call_id+'"]');
            link.closest('.pgn-wrapper').remove();
        }
    });
    
    // socket.on('new_contract', function (data) {
    //     var $message_content = $('<div></div>');
    //     $message_content.append("<div class='alert-header'>Новый договор</div>");
    //     showNotification('body', $message_content.html(), 'bottom-right', 'info', 'circle',30000);
    //     var $count_countent = $('ul.menu-items .js-contract_count');
    //     var contract_count = parseInt($count_countent.text());
    //     $count_countent.text(++contract_count);
    // });
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
