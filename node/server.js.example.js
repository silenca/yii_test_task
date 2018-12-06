var express = require('express');
var config = require('./config.js');
var io = require('socket.io').listen('8005');
console.log('Start socket on: ' + config.socket.port);
var bodyParser = require('body-parser');
var http = require('http');

var clients = [];


/*   error handler START */
function handler (req, res) {
  fs.readFile(__dirname + '/index.html',
  function (err, data) {
    if (err) {
      res.writeHead(500);
      return res.end('Error loading index.html');
    }

    res.writeHead(200);
    res.end(data);
  });
}
/*   error handler END */

/* on connected to socket server START */
io.sockets.on('connection', function (socket) {
    socket.on('join', function (data) {
        if (data.notify_id && data.role_id && data.notify_id.length === 32) {
            switch (data.role_id) {
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
    }); //Checking user role
    socket.on('disconnect', function () {
        var index = clients.indexOf(socket);
        if (index !== -1) {
            socket.leave(socket.room);
            clients.splice(index, 1);
        }
    }); //Checking is user disconnected
});
/* on connected to socket server END */

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
    io.to('operator').emit('close_call', { call_id: req.body.call_id });
    io.to('manager').emit('close_call', { call_id: req.body.call_id });
    // io.to('admin').emit('close_call', {call_id:req.body.call_id});
});

app.post('/incoming', function (req, res) {
    var data = {
        'contact_name': req.body.contact_name,
        'phone': req.body.phone,
        'call_id': req.body.call_id,
        // 'language': req.body.language,
        'id': req.body.id
    };
    if (req.body.attraction_channel_id !== undefined)
        data.attraction_channel_id = req.body.attraction_channel_id;
    io.to('operator').emit('call_incoming', data);
    io.to('manager').emit('call_incoming', data);
    // io.to('admin').emit('call_incoming', data);
    console.log("Incoming call: " + req.body.phone);
    res.send('Сообщение отправлено всем операторам');
});


const server = app.listen(config.app.port, '127.0.0.1', function () {
    let host = server.address().address;
    let port = server.address().port;
    console.log('Example app listening at http://%s:%s', host, port);
});