var readyCallback = function (e) {
    createSipStack();
};
var errorCallback = function (e) {
    console.error('Failed to initialize the engine: ' + e.message);
};
SIPml.init(readyCallback, errorCallback);
var sipStack;
var eventsListener = function (e) {
    if (e.type == 'started') {
        login();
    }
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
}

sipStack.start();

var registerSession;
var eventsListener = function (e) {
    console.info('session event = ' + e.type);
    if (e.type == 'connected' && e.session == registerSession) {
        makeCall();
    }
};
var login = function () {
    registerSession = sipStack.newSession('register', {
        events_listener: {events: '*', listener: eventsListener} // optional: '*' means all events
    });
    registerSession.register();

};
// var timerMain = function(e, el){
//     var seconds = 0, minutes = 0, hours = 0,
//         t;
//
//     function add() {
//         seconds++;
//         if (seconds >= 60) {
//             seconds = 0;
//             minutes++;
//             if (minutes >= 60) {
//                 minutes = 0;
//                 hours++;
//             }
//         }
//
//         el.text((hours ? (hours > 9 ? hours : "0" + hours) : "00") + ":" + (minutes ? (minutes > 9 ? minutes : "0" + minutes) : "00") + ":" + (seconds > 9 ? seconds : "0" + seconds));
//
//         timer();
//     }
//     function timer() {
//         t = setTimeout(add, 1000);
//     }
//     if(e === 'start'){
//         timer();
//     }else{
//         clearTimeout(t);
//     }
//
// };
var callSession;
var eventsListener = function (e) {
    console.info('session event = ' + e.type);
    var text = e.type;
    switch (text) {
        case 'connecting':
            text = 'Соединение';
            $('.acb-hang-up-btn').click(function(){
                callSession.hangup();
            });
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
            $('.acb-call-btn').enable();
            $('.acb-hang-up-btn').disable();
            // timerMain('stop', $('.acb-duration'));
            break;
    }
    $('.audio-call-messages .acb-status').text(text);
};

var makeCall = function (phone_number) {
    callSession = sipStack.newSession('call-audio', {
        audio_remote: document.getElementById('audio-remote'),
        events_listener: {events: '*', listener: eventsListener} // optional: '*' means all events
    });
    callSession.call(phone_number);

};

