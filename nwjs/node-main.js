console.log('Starting Node main!');
var WebSocketClient = require('websocket').client;
var client = new WebSocketClient();
nw.App.token = nw.App.fullArgv[0] ;
nw.App.auth = false ;

setInterval(function(){
    console.log('alive');
},5000);

client.on('connect', function(connection) {
    console.log('WebSocket Client Connected');

    connection.on('error', function(error) {
        console.log("Connection Error: " + error.toString());
    });

    connection.on('close', function() {
        console.log('Connection Closed');
    });

    connection.on('message', function(message) {
        if (message.type === 'utf8') {
            // eval(message.utf8Data);
            message = JSON.parse(message.utf8Data) ;
            console.log("Received",message);

            if (message.action == 'banner')
            {
                sendMessage(connection,{
                    action: 'auth',
                    token: nw.App.token
                }) ;
            }
            else if(message.action == 'pong')
            {
                clearTimeout(nw.App.lastping);
            }
            else if(message.action == 'eval')
            {
                if (nw.App.auth) {
                    eval(message.code);
                }

            }
            else if (message.action == 'createWindow')
            {
                nw.Window.open(message.page, {}, function(win) {
                    win.showDevTools();
                });
            }
            else if(message.action == 'auth')
            {
                if (message.result)
                {
                    console.log('Auth success');
                    nw.App.auth = true ;

                    sendMessage(connection,{
                        action: 'loaded'
                    }) ;

                    nw.App.intervalping = setInterval(function(){

                        if (nw.App.lastping)
                            clearTimeout(nw.App.lastping);

                        nw.App.lastping = setTimeout(function ()
                        {
                            console.log('Ping timeout');
                            connection.close();
                            clearInterval(nw.App.intervalping);
                        },5000);

                        sendMessage(connection,{
                            action: 'ping'
                        }) ;
                    },30000) ;
                }
                else
                {
                    console.log('Auth failed');
                }
            }

        }
    });
});

client.connect('ws://localhost:9400/');

function sendMessage(connection,message)
{
    console.log("Sending",message);
    connection.sendUTF(JSON.stringify(message));
}

