
var sipStack;
var isMuted = false; // store current status of audio mute
var ongoing_session; // session to store incoming and outgoing calls
var incomingCallEvent; // to get incomming call event
ringTone.loop      = true;
var eventsListener = function (e) {
    // console.log(e);
    // console.log('%c begning=>' + e.type, 'font-size:5em');
    if (e.type == 'started') {
        login();
    }
    if (e.type == 'i_new_call') {
        incomingCallEvent = e;
        ongoing_session   = e.newSession;
        ringTone.play();
        $('#wrapper').modal();
        $.getJSON('/contacts/get-contact-by-phone', {phone:ongoing_session.getRemoteFriendlyName()}, function (response) {
            $.getJSON('/contacts/view' , {id:response.data.contact_id} , function(response){
                $('#contact-call').html(response.data.surname + ' ' + response.data.name + ' ' + response.data.middle_name);
            })
        });
        console.log($('#contact-call'))
        $('#contact-call').on('click', function (e){
            $.getJSON('/contacts/get-contact-by-phone', {phone:ongoing_session.getRemoteFriendlyName()}, function (response) {
                openContactForm (response.data.contact_id);
            });
        });
        $('#answer').on('click', function (e){
            $.getJSON('/contacts/get-contact-by-phone', {phone:ongoing_session.getRemoteFriendlyName()}, function (response) {
                openContactForm (response.data.contact_id);
            });
            $('.modal-backdrop').css('display', 'none');
            $('#wrapper > .modal-body').css('display', 'none');
            $('#reject').remove();
            $('#reject-wrap').append("<div id ='reject'><i class='fa fa-phone'></div>")
        });
        $('#reject-wrap').on('click', function(){
            $('#reject-wrap').remove();
            $('#incomingCall').append("<div id ='reject'><i class='fa fa-phone'></div>")
        });
    }
};

function createSipStack() {
    sipStack = new SIPml.Stack({
            realm: 'dopomogaplus.silencatech.com', // mandatory: domain name
            impi: '600', // mandatory: authorization name (IMS Private Identity)
            impu: 'sip:600@dopomogaplus.silencatech.com:8088', // mandatory: valid SIP Uri (IMS Public Identity)
            password: 'YAahWJQsGE7lF5d', // optional
            display_name: '600', // optional
            websocket_proxy_url: 'wss://dopomogaplus.silencatech.com:8089/ws', // optional
            outbound_proxy_url: 'udp://dopomogaplus.silencatech.com:5060', // optional
            enable_rtcweb_breaker: true, // optional
            disable_video: true,
//              ice_servers:           [{"url": "stun:stun.l.google.com:19302"}],
            events_listener: { events: '*', listener: eventsListener },
            enable_media_stream_cache: true,
            enable_early_ims: false,
            sip_headers: [ // optional
                { name: 'User-Agent', value: 'IM-client/OMA1.0 sipML5-v1.0.0.0' },
                { name: 'Organization', value: 'Doubango Telecom' }
            ]
        }
    );
}
createSipStack();
sipStack.start();

var login      = function () {
    var registerSession = sipStack.newSession('register', {
        events_listener: {events: '*', listener: eventsListener}
    });
    registerSession.register();
};
var makeCall   = function (number) {
    var outCallSession = sipStack.newSession('call-audio', {
        audio_remote:    document.getElementById('audio-remote'),
        events_listener: {
            events: '*', listener: function (e) {
                // console.log(e);
                // console.log('%c OUT = >' + e.type, 'font-size:5em');
                if (e.type === 'connected') {
                    $('#callInfoTextOut').html('Начался звонок...');
                    $('#callInfoNumberOut').html(number);
                }
                else if (e.type === 'm_stream_audio_remote_added') {
                    domInCall();
                }
                else if (e.type === 'terminated') {
                    ongoing_session = false;
                    domHangup();
                }
            }
        }
    });
    outCallSession.call(number);
    ongoing_session = outCallSession;
};
var acceptCall = function (e) {
    var incoming_audio_configuration = {
        audio_remote:    document.getElementById('audio-remote'),
        expires:200,
        events_listener: {
            events: '*', listener: function (e) {
                // console.log('%c In = >' + e.type, 'font-size:5em');
                if (e.type === 'm_stream_audio_remote_added') {
                    domInCall();
                }
                else if (e.type === 'terminated') {
                    domHangup();
                    ongoing_session = false;
                }
            }
        }
    };
    e.newSession.accept(incoming_audio_configuration);
};

$('.acb-call-btn').click(function () {
    var number = $('.acb-call-btn').parents('.contact-modal').find('#contact_phones').val();
    $('#callInfoTextOut').html('Ожидайте соединения...');
    $('.acb-call-btn').attr('disabled', true);
    $('.acb-hang-up-btn').attr('disabled', false);
    $('#callInfoNumberOut').html(number);
    makeCall(number);
});
$('#answer').click(function () {
    acceptCall(incomingCallEvent);
});
var hangup    = function () {
    domHangup();
    ongoing_session.hangup();
};
var domHangup = function () {
    ringTone.pause();
    isMuted = false;
    $('.acb-hang-up-btn').attr('disabled', true);
    $('.acb-call-btn').attr('disabled', false);
    $('#wrapper').modal('hide');
    $('#muteIcon').removeClass('fa-microphone-slash');
    $('#muteIcon').addClass('fa-microphone');
    $('#incomingCall').hide();
    $('#callControl').show();
    $('#callControlOut').show();
    $('#callStatusOut').hide();
    // $('.audio-call-actions').hide();
};
var domInCall = function () {
    ringTone.pause();
    $('.acb-call-btn').attr('disabled', true);
    $('.acb-hang-up-btn').attr('disabled', false);
    // $('#callStatus').show();
    $('#callStatusOut').show();
    $('#incomingCall').show();

    // $('#callInfoText').html('Установка соединения...');
    $('#callInfoTextOut').html('Вызываем...');
    if(ongoing_session.getRemoteFriendlyName()){
    $('#callInfoNumber').html(ongoing_session.getRemoteFriendlyName());
    }
    // $('#callInfoNumberOut').html(ongoing_session.getRemoteFriendlyName());
    $('.audio-call-actions').show();
};
// $('#hangUp').click(hangup);
$('#hangup_btn').click(hangup);
$('#reject').click(hangup);
$('#mute').click(function () {
    isMuted = isMuted ? false : true;
    ongoing_session.mute('audio', isMuted);
    if (isMuted) {
        $('#muteIcon').addClass('fa-microphone-slash');
        $('#muteIcon').removeClass('fa-microphone');
    } else {
        $('#muteIcon').removeClass('fa-microphone-slash');
        $('#muteIcon').addClass('fa-microphone');
    }
});
$('div.cs-options li[data-value=call]').click(function () {
    $('.btn-audio-call').click(function () {

        $('.audio-call-messages').show();

        $('.acb-call-btn').click();

    })
});

$('#inCallButtons').on('click', '.dialpad-char', function (e) {
    dtmfTone.play();
    var $target = $(e.target);
    var value   = $target.data('value');
    ongoing_session.dtmf(value.toString());
});
window.onbeforeunload = function (event) {
    if (ongoing_session) {
        sipStack.stop();
    }
};
