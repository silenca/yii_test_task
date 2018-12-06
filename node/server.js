var express = require('express');
var config = require('./config.js');
var io = require('socket.io').listen(config.socket.port);
console.log('Start socket on: '+config.socket.port);
var mysql = require('mysql');
var bodyParser = require('body-parser');

var http = require('http');

var socketE = '';

var clients = [];
io.sockets.on('connection', function (socket) {
    socket.join('manager');
    console.log('manager');
    socket.on('join', function (data) {
                    switch(data.role_id){
                        case 1:
                            socket.join('operator');
                            break;
                        case 5:
                            socket.join('manager');
                            socketE = socket;
                            break;
                        case 15:
                            socket.join('admin');
                            break;
                }

            })
    socket.on('disconnect', function () {
        var index = clients.indexOf(socket);
        if (index !== -1) {
            socket.leave(socket.room);
            clients.splice(index, 1);
        }
    })

    })




var app = express();

app.use(bodyParser.json());       // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({// to support URL-encoded bodies
    extended: true
}));

//getOnlineUsers();

app.get('/toadmin', function (req, res) {
    res.send('Сообщение отправлено всем админам');
}); 

app.post('/close-call', function (req, res) {
    io.to('operator').emit('close_call', {call_id:req.body.call_id});
    io.to('manager').emit('close_call', {call_id:req.body.call_id});
    io.to('admin').emit('close_call', {call_id:req.body.call_id});
});

app.post('/incoming', function (req, res) {
    var data = {
        'contact_name': req.body.contact_name,
        'phone': req.body.phone,
        'call_id':req.body.call_id,
        'id': req.body.id
    };
   
    io.to('operator').emit('call_incoming', data)
    io.to('manager').emit('call_incoming', data);
    io.to('admin').emit('call_incoming', data);
    console.log(data);
    console.log("Incoming call: "+req.body.phone);
    res.send('Сообщение отправлено всем операторам');
});


var server = app.listen(config.app.port, '127.0.0.1', function () {

    var host = server.address().address;
    var port = server.address().port;

    console.log('Example app listening at http://%s:%s', host, port);

});

//function getOnlineUsers() {
//    var options = {
//        host: config.crm.host,
//        port: config.crm.host.port,
//        path: '/api/getonlineusers'
//    };
//
//    http.get(options, function (res) {
//        console.log("Got response: " + res.statusCode);
//        res.on('data', function (data) {
//            var result = JSON.parse(data);
//            console.log('SELECT `user`.`role`,`user`.`notification_key` from `user` where `user`.`id` IN (' + result.data + ')');
//            connection.query('SELECT `user`.`notification_key` from `user` where `user`.`id` IN (' + result.data + ')', function (err, rows, fields) {
//                if (!err) {
//                    rows.forEach(function (row) {
//                        switch (row.role) {
//                            case 1:
//                                io.join('manager');
//                                break;
//                            case 5:
//                                io.join('supervisor');
//                                break;
//                            case 10:
//                                io.join('fin_dir');
//                                break;
//                            case 15:
//                                io.join('admin');
//                                break;
//                        }
//                    });
//
//                }
//                else {
//                    console.log('Error while performing Query.', err);
//                }
//            });
//        });
//    }).on('error', function (e) {
//        console.log("Got error: " + e.message);
//    });
//}

