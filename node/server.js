var express = require('express');
var config = require('./config.js');
var io = require('socket.io').listen(config.socket.port);
var mysql = require('mysql');
var bodyParser = require('body-parser');

var http = require('http');

var connection = mysql.createConnection(config.mysql);

function handleDisconnect(conn) {
    conn.on('error', function (err) {
        if (!err.fatal) {
            return;
        }

        if (err.code !== 'PROTOCOL_CONNECTION_LOST') {
            throw err;
        }
        console.log('Re-connecting lost connection: ' + err.stack);
        connection = mysql.createConnection(config.mysql);
        handleDisconnect(connection);
        //connection.connect();
    });
}
handleDisconnect(connection);

var clients = [];
io.sockets.on('connection', function (socket) {
    clients.push(socket);
    socket.on('disconnect', function () {
        var index = clients.indexOf(socket);
        if (index != -1) {
            socket.leave(socket.room);
            clients.splice(index, 1);
        }
    });

    socket.on('join', function (data) {
        if (data.notify_id && data.notify_id.length === 32) {
            connection.query('SELECT `user`.`role` from `user` where `notification_key` = "' + data.notify_id + '" LIMIT 1', function (err, rows, fields) {
                if (!err) {
                    switch (rows[0].role) {
                        case 1:
                            socket.join('operator');
                            break;
                        case 5:
                            socket.join('manager');
                            break;
                        case 15:
                            socket.join('admin');
                            break;
                    }
                }
                else {
                    console.log('Error while performing Query.', err);
                }
            });
        }
    });
});


var app = express();

app.use(bodyParser.json());       // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({// to support URL-encoded bodies
    extended: true
}));

//getOnlineUsers();

app.get('/toadmin', function (req, res) {
    res.send('Сообщение отправлено всем админам');
});

app.post('/incoming', function (req, res) {
    var data = {
        'contact_name': req.body.contact_name,
        'phone': req.body.phone,
        // 'language': req.body.language,
        'id': req.body.id
    };
    io.to('operator').emit('call_incoming', data);
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
