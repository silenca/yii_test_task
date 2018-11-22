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

var callSession;
var eventsListener = function (e) {
    console.info('session event = ' + e.type);
};

var makeCall = function (phone_number) {
    callSession = sipStack.newSession('call-audio', {
        audio_remote: document.getElementById('audio-remote'),
        events_listener: {events: '*', listener: eventsListener} // optional: '*' means all events
    });
    callSession.call(phone_number);
};
