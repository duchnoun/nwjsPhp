<?php

use Castor\Attribute\AsTask;
use React\EventLoop\Loop ;
use function Castor\io ;
use React\ChildProcess\Process ;
include 'websocketServer.php';

#[AsTask(description: 'Main !')]
function main(): void
{
    // Need to install : frankenphp
    // Need to install : nw
    // Need to install : maybe npm ?
    // Need to install : maybe nodejs ?
    // Need to install : maybe castor ?
    // need to launch : npm install in nwjs

    $loop = Loop::get();
    $TOKEN = md5(uniqid());
    $process = new Process('../../frankenphp php-server --listen 127.0.0.1:8080',__DIR__ . '/app/public'); // todo trouver mieux ..
    $process->start($loop);

    $nwjsServer = new NwJsServerWS($TOKEN,$loop) ;
    $nwjsServer->on('message',function ($message)
    {
        io()->note('Message received : ' . json_encode($message) );
    }) ;

    $nwjsServer->on('loaded',function() use ($nwjsServer,$loop)
    {

//        $http = new React\Http\HttpServer(function (Psr\Http\Message\ServerRequestInterface $request) {
//            return React\Http\Message\Response::html(
//                file_get_contents('./nwjs/index.html'),
//            );
//        });
//
//        $socket = new React\Socket\SocketServer('127.0.0.1:9401');
//        $http->listen($socket);
//
//
//        $http = new React\Http\HttpServer(function (Psr\Http\Message\ServerRequestInterface $request) {
//            return React\Http\Message\Response::plaintext(
//                'window 2'
//            );
//        });
//
//        $socket = new React\Socket\SocketServer('127.0.0.1:9402');
//        $http->listen($socket);

        $nwjsServer->createWindow('http://localhost:8080/');

//        $nwjsServer->createWindow('http://localhost:9401/');
//        $nwjsServer->createWindow('http://localhost:9402/');

        $code = file_get_contents('nwMenu.js');
        $nwjsServer->nwjsEval($code);
    }) ;

    $nwjsServer->on('nwjsEvent',function ($type,$payload)
    {
        if (in_array($type,['stdout_data','stderr_data']))
            io()->warning($payload['data']);
        elseif ($type == 'exit')
        {
            io()->error('Process exited with code : ' . $payload['exitCode'] . ' and signal : ' . $payload['termSignal']);
            io()->error('Exiting castor ...');
            die();
        }
    }) ;

    $loop->addPeriodicTimer(2, function () use ($nwjsServer) {
        echo "2 second passed\n";
    });

    $nwjsServer->run();
}
