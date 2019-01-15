$(function(){
    if(!$('.sip_config').length) {
        return;
    }

    var ctrl = new CallController('body');

    $('table').on('click', '.contact_open_disable', function(){
        var contactId = $(this).closest('[data-id]').data('id'),
            number = $(this).html();

        ctrl.doCall(number);
        ctrl.showCard(number);

        if(contactId) {
            ctrl.updateCallerId(contactId);
        }

        $.getJSON('/contacts/get-contact-by-phone', {
            phone: number
        }, function(response) {
            if(response.data) {
                ctrl.updateCallerName(response.data.full_name);
            }
        });

        openContactForm(contactId);
        changeActionsForm('call');
    });

    $('body').on('click', '.btn-audio-call', function(){
        var contact = $(this).data();
        if(!contact.id || !contact.number) {
            return;
        }

        ctrl.doCall(contact.number);
        ctrl.showCard(contact.number);
        ctrl.updateCallerId(contact.id);

        $.getJSON('/contacts/get-contact-by-phone', {
            phone: contact.number
        }, function(response) {
            if(response.data) {
                ctrl.updateCallerName(response.data.full_name);
            }
        });
    });

    $('.incoming_call_wrapper').on('click', function(e){
        if($(e.target).is('.action')) {
            return false;
        }

        var contactId = ctrl.getCallerId();
        if(contactId) {
            openContactForm(contactId);
        } else {
            openNewContactForm(ctrl.getCurrentPhoneNumber());
        }

        changeActionsForm('call');

        ctrl.doAccept();
    });

    $('.incoming_call_wrapper').on('click', '.action_end_call', function(){
        ctrl.doReject();
    });

    $('body').on('income_new.caller', function(){
        var ctrl = $('body').data('callCtrl');
        var number = ctrl.getCurrentPhoneNumber();
        if(number) {
            $.getJSON('/contacts/get-contact-by-phone', {
                phone: number
            }, function (response) { // getting full name of incoming contact
                if(response.data) {
                    ctrl.updateCallerId(response.data.contact_id);
                    ctrl.updateCallerName(response.data.full_name);
                }
            });
        }
    });

    (function(){
        var intervalId, started = 0, ONE_MINUTE = 60;
        var prettyTime = function(val) {
            var strVal = ''+val;
            return (strVal.length == 1)?('0'+strVal):strVal;
        };

        var durationUpdater = function(){
            var duration = started?(new Date().getTime() - started):0;

            setCallTime(Math.round(duration/1000));
        };

        var setCallTime = function(durationSeconds){
            var minutes = (durationSeconds - (durationSeconds % ONE_MINUTE))/ONE_MINUTE,
                seconds = durationSeconds - minutes*ONE_MINUTE;

            ctrl.updateCardTimer(_.map([
                minutes,
                seconds
            ], prettyTime).join(':'));
        };

        $('body').on('callstarted.caller', function(){
            started = new Date().getTime();
            intervalId = setInterval(durationUpdater, 500);
        });

        $('body').on('hangup.caller', function(){
            clearInterval(intervalId);
            setCallTime(started = 0);
        });
    })();
});

function CallController(wr, opts, handles) {
    var self = this;
    var options = _.extend({
        wrapper: wr,
        sipConfig: {
            realm: 'dopomogaplus.silencatech.com',
            websocket_proxy_url: 'wss://dopomogaplus.silencatech.com:8089/ws',
            outbound_proxy_url: 'udp://dopomogaplus.silencatech.com:5060',
            enable_rtcweb_breaker: true,
            disable_video: true,
            ice_servers: [{"url": "stun:stun.l.google.com:19302"}],
            disable_debug: true,
            enable_media_stream_cache: true,
            enable_early_ims: false,
            sip_headers: [
                { name: 'User-Agent', value: 'IM-client/OMA1.0 sipML5-v1.0.0.0' },
                { name: 'Organization', value: 'Doubango Telecom' }
            ]
        },
        selectors: {
            sipData: '.sip_config',
            card: {
                wrapper: '.incoming_call_wrapper',
                title: '.action_holder',
                name: '.name_holder',
                number: '.number_holder',
                id: '.id_holder',
                timer: '.timer_holder'
            }
        },
        titles: {
            incoming: 'Входящий вызов от:',
            outcoming: 'Вы вызываете:'
        },
        tones: {
            default: './media/sounds/ringtone.mp3'
        }
    }, opts || {});
    var handlers = _.extend({
        login: function() {
            sip.newSession('register', {
                events_listener: self.buildListener('login')
            }).register();
        },
        hangup: function(){
            self.trigger('hangup');
            session = null;
        },
        call: function(number){
            session = sip.newSession('call-audio', {
                audio_remote: document.getElementById('audio-remote'),
                events_listener: self.buildListener('outcoming', {
                    connected: function(){
                        self.trigger('outcome_new');
                        self.trigger('callstarted');
                    },
                    m_stream_audio_remote_added: function(){
                        self.trigger('outcome_new');
                    },
                    terminated: function(){
                        handlers.hangup();
                    }
                })
            });
            session.call(number);

            self.trigger('calling');
        },
        card: {
            mode: function(mode){
                if(mode === undefined) {
                    return cardMode;
                } else {
                    cardMode = mode;
                }

                self.updateCallerData('title')(options.titles[cardMode]);
            },
            show: function (number, name) {
                self.updateCallerNumber(number);
                self.updateCallerName(name || '');
                self.updateCallerId(0);

                self.getCardWrapper().show();
            },
            hide: function () {
                self.getCardWrapper().hide();
            },
            updateTimer: function(val){
                $(options.selectors.card.timer, self.getCardWrapper()).html(val);
            }
        }
    }, handles || {});
    var wrapper = null;
    var cardWrapper = null;
    var sip;
    var session;
    var tones = {};
    var cardMode;

    this.getWrapper = function(){
        if(_.isNull(wrapper)) {
            wrapper = $(options.wrapper);
            if(!wrapper.length) {
                throw 'Invalid wrapper provided';
            }
        }
        return wrapper;
    };

    this.getCardWrapper = function(){
        if(_.isNull(cardWrapper)) {
            cardWrapper = $(options.selectors.card.wrapper);
            if(!cardWrapper.length) {
                throw 'Invalid card wrapper provided';
            }
        }
        return cardWrapper;
    };

    this.buildListener = function(name, cbConfig) {
        var callbacks = _.extend({}, cbConfig || {});

        return {
            events: '*',
            listener: function(e) {
                console.log('[L]', '['+name.toUpperCase()+']', e.type);
                var callback = callbacks[e.type] || null;
                if(typeof callback === 'function') {
                    return callback(e);
                }

                return;
            }
        };
    };

    this.init = function() {
        _.each(options.tones, function(url, name){
            tones[name] = new window.Audio(url);
            tones[name].loop = true;
        });

        var sipConfig = $(options.selectors.sipData).data(),
            login = sipConfig.login || '',
            password = sipConfig.password || '';

        sip = new SIPml.Stack(_.extend(options.sipConfig, {
            impi: ''+login,
            impu: 'sip:'+login+'@dopomogaplus.silencatech.com:8088',
            password: password,
            display_name: ''+login,
            events_listener: self.buildListener('main', {
                started: handlers.login,
                i_new_call: function(e){
                    if(session) {
                        // We already have active call
                        // Hangup new one
                        e.newSession.hangup();
                        return;
                    }

                    e.newSession.setConfiguration({
                        events_listener: self.buildListener('incoming', {
                            terminated: function() {
                                handlers.hangup();
                                console.log('Incoming terminated');
                            }
                        })
                    });

                    session = e.newSession;

                    self.trigger('income_new');
                }
            })
        }));

        sip.start();

        return self;
    };

    this.play = function(name) {
        if(tones.hasOwnProperty(name)) {
            tones[name].play().catch(function(){ console.log('Can not play tone "'+name+'"'); });
        }
        return self;
    };

    this.pause = function(name) {
        if(tones.hasOwnProperty(name)) {
            tones[name].pause();
            tones[name].currentTime = 0;
        }
        return self;
    };

    this.doCall = function(number) {
        handlers.card.mode('outcoming');
        self.showCard(number);
        handlers.call(number);
    };

    this.doAccept = function(){
        if(!session) {
            return;
        }

        session.accept({
            audio_remote: document.getElementById('audio-remote'),
            expires: 200,
            events_listener: self.buildListener('accepted', {
                terminated: function(){
                    handlers.hangup();
                }
            })
        });

        self.trigger('callstarted');
        self.trigger('accepted');
    };

    this.doReject = function(){
        if(!session) {
            return;
        }

        session.hangup();

        handlers.hangup();
    };

    this.getCurrentPhoneNumber = function(){
        if(session) {
            return session.getRemoteFriendlyName();
        }

        return null;
    };

    this.updateCallerData = function(field){
        return function(value){
            $(options.selectors.card[field], self.getCardWrapper()).html(value);
        };
    };

    this.getCallerId = function(){
        return $(options.selectors.card.id, self.getCardWrapper()).html();
    };

    this.updateCallerId = self.updateCallerData('id');
    this.updateCallerName = self.updateCallerData('name');
    this.updateCallerNumber = self.updateCallerData('number');

    this.trigger = function(type, data){
        console.log('[T]', type.toUpperCase(), data);
        self.getWrapper().trigger(type+'.caller', _.extend({
            ctrl: self
        }, data || {}));
    };

    this.showCard = handlers.card.show;
    this.hideCard = handlers.card.hide;
    this.updateCardTimer = handlers.card.updateTimer;

    // Assign handlers
    self.getWrapper().on('income_new.caller', function(){
        var number = self.getCurrentPhoneNumber();
        if(!number) {
            throw 'Invalid session data for incoming call';
        }

        self.play('default');

        handlers.card.mode('incoming');

        self.showCard(number);
    });

    self.getWrapper().on('calling.caller', function(){
        self.play('default');
    });

    self.getWrapper().on('outcome_new.caller', function(){
        self.pause('default');
    });

    self.getWrapper().on('hangup.caller accepted.caller', function(){
        _.each(tones, function(tone, name){
            self.pause(name);
        });
    });

    self.getWrapper().on('hangup.caller', function(){
        self.hideCard();
    });

    // Block page reload if have active session
    window.onbeforeunload = function(e){
        if(session) {
            var message = "Перезагрузка страницы приведет к сбросу текущего звонка. Вы уверены, что хотите перезагрузить страницу?";
            if (typeof e == "undefined") {
                e = window.event;
            }
            if(e) {
                e.returnValue = message;
            }
            return message;
        } else {
            return null;
        }
    };

    self.init();

    self.getWrapper().data('callCtrl', self);
};