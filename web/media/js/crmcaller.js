var readyCallback = function (e) {
    createSipStack();
};
var errorCallback = function (e) {
    console.error('Failed to initialize the engine: ' + e.message);
};
SIPml.init(readyCallback, errorCallback);
var sipStack;
var eventsListener = function (e) {
    if(e.type == 'createAnswer' && e.session == registerSession) {
        showAnswerNotification(e)
    }
if (e.type=="__tsip_transport_ws_onmessage"){
    showAnswerNotification(e);
}

};
var registerSession;
function showAnswerNotification(data) {
    console.log("Incoming call:");
    console.log('data');
    console.log(data);
    var $message_content = $('<div></div>');
    $message_content.append("<div class='alert-header'>Входящий вызов</div>");
    if (data.id) {
        $('.alert-link').click(Answer());
        $message_content.append("<div class='alert-link'><span>" + data.contact_name + "</span></div>");
        $message_content.append("<div class='alert-details'><span>Номер: </span><a href='javascript:void(0)' data-contact-id='" +
            data.id +
            "' class='notification-open-contact' data-call-id='"+data.call_id+"'>"
            + data.phone + "</a></div>");
    } else {
        $message_content.append("<div class='alert-link'><a href='javascript:AnswerCall();' class='notification-open-new-contact' data-attraction-channel-id='" +
            (data.attraction_channel_id!==undefined?data.attraction_channel_id:'') +
            "' data-call-id=1'"+data.call_id+"'>" + data.phone + "</a></div>");

    }

    showNotification('body', $message_content.html(), 'bottom-right', 'info', 'circle',0);
}

var eventsListener = function (e) {
    console.info('session event = ' + e.type);

    if (e.type == 'started') {
        login();
    }
    if (e.type == 'connected' && e.session == registerSession) {
        showAnswerNotification(e)
    }
    if(e.type == 'createAnswer' && e.session == registerSession) {
        showAnswerNotification(e)
    }
    if(e.type == '__on_add_stream'){
        showAnswerNotification(e);
    }

   console.log('event', e);


};
function createSipStack() {
    var configSip = {
        realm: 'dopomogaplus.silencatech.com:8088', // mandatory: domain name
        impi: '600', // mandatory: authorization name (IMS Private Identity)
        impu: 'sip:600@dopomogaplus.silencatech.com:8088', // mandatory: valid SIP Uri (IMS Public Identity)
        password: 'YAahWJQsGE7lF5d', // optional
        display_name: '600', // optional
        websocket_proxy_url: 'wss://dopomogaplus.silencatech.com:8089/ws', // optional
        enable_rtcweb_breaker: true, // optional
        disable_video: true,
        enable_media_stream_cache: false,
        enable_early_ims: false,
        sip_headers: [ // optional
            {name: 'User-Agent', value: 'IM-client/OMA1.0 sipML5-v1.0.0.0'},
            {name: 'Organization', value: 'Doubango Telecom'}
        ]
    };
    if (eventsListener) {
        configSip.events_listener = {events: '*', listener: eventsListener};
    }
    sipStack = new SIPml.Stack(configSip);

    sipStack.start();
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    async function waitreg(number, callback) {
        await sleep(2000);
        login()
        $('.acb-call-btn').click(function (e) {
            e.preventDefault();
            $(this).attr('disabled', true);
            $('.acb-hang-up-btn').attr('disabled', false);
            var phone_number = $('#contact_phones').val();
            if (phone_number.startsWith('+380')) {
                phone_number = phone_number.slice(3);
            } else if (phone_number.startsWith('0') && phone_number.length !== 10) {
                phone_number = phone_number.trim();
            }
            makeCall(phone_number);
            $('.acb-hang-up-btn').click(function(){

                $(this).attr('disabled', true);
                $('.acb-call-btn').attr('disabled', false);
                hangUp();
            });
        })
    }
    waitreg(2, 'slksad')


}

// sipStack.start();


var login = function () {
    registerSession = sipStack.newSession('register', {
        events_listener: {events: '*', listener: eventsListener} // optional: '*' means all events
    });
    // sipStack.start();
    registerSession.register();

};

var callSession;
var     eventsListener = function (e) {
    console.info('session event = ' + e.type);
    console.log('all events = ', e.type, e);

    var text = e.type;
    switch (text) {
        case 'connecting':
            text = 'Соединение';
            // $('.acb-call-btn').attr('disabled', true);
            // $('.acb-hang-up-btn').attr('disabled', false);

            break;
        case 'm_stream_audio_remote_added':
            text = 'Соединение';
            break;
        case 'connected':
            text = 'Звонок начался';
            if (e.type === 'connected')
            // timerMain('start', $('.acb-duration'));
                break;
        case 'terminated':
            text = 'Завершен';
            $('.acb-call-btn').attr('disabled', false);
            $('.acb-hang-up-btn').attr('disabled', true);
            // timerMain('stop', $('.acb-duration'));
            break;
        case e.o_event.e_type == 20 && e.o_event.e_type == 901:
            showAnswerNotification(e);
            break;
    }
    $('.audio-call-messages .acb-status').text(text);
};
var makeCall = function (phone_number) {
    createSipStack();

    callSession = sipStack.newSession('call-audio',{
        audio_remote: document.getElementById('audio-remote'),
        events_listener: {events: '*', listener: eventsListener} // optional: '*' means all events
    });
    callSession.call(phone_number);

};

var hangUp = function(){
    createSipStack();

    callSession.hangup({ events_listener: { events: '*', listener: eventsListener } });
};
var AnswerCall = function(){
    createSipStack();
    callSession = sipStack.newSession('call-audio', {
        audio_remote: document.getElementById('audio-remote'),
        events_listener: {events: '*', listener: eventsListener} // optional: '*' means all events
    });
    callSession.accept()
}