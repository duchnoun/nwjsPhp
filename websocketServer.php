<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\ChildProcess\Process ;
use Ratchet\Http\HttpServer ;
use Ratchet\WebSocket\WsServer ;
use Ratchet\Server\IoServer ;
use React\EventLoop\LoopInterface  ;
use Evenement\EventEmitterTrait ;
use React\Socket\SocketServer;

class NwJsServerWS implements MessageComponentInterface {
    use EventEmitterTrait ;
    protected $clients;

    public function run()
    {
        $this->loop->run();
    }

    public function __construct(private string $TOKEN,private LoopInterface $loop) {
        $this->clients = new \SplObjectStorage;
        $this->lastAuthClient = null ;

        $component = new HttpServer(
            new WsServer(
                $this
            )
        ) ;

        $reactor = new SocketServer('127.0.0.1:9400',[], $loop) ;
        new IoServer($component,$reactor,$loop); // attach serverWs to Loop
        $process = new Process('sudo pkill nw ; sleep 1 ; sudo pkill nw; /home/david/poc/nwjsPhp/nwjs-sdk-v0.92.0-linux-x64/nw .  ' . $TOKEN ,__DIR__ . '/nwjs/'); // todo trouver mieux ..
        $process->start($loop);

        $process->stdout->on('data', function ($data) use ($process) {
            $this->emit('nwjsEvent',['type' => 'stdout_data', 'payload' => ['data' =>$data ]]) ;
        });
        $process->stderr->on('data', function ($data) {
            $this->emit('nwjsEvent',['type' => 'stderr_data', 'payload' => ['data' =>$data ]]) ;
        });
        $process->on('exit', function($exitCode, $termSignal) {
            $this->emit('nwjsEvent',['type' => 'exit', 'payload' => ['exitCode' =>$exitCode,'termSignal' => $termSignal]] );
        });
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $conn->auth = false ;
        $conn->send(json_encode(['action' => 'banner' , 'message' => 'Welcome to Ws Server NwjsPHP Binding !' ]));

    }

    public function onMessage(ConnectionInterface $from, $message) {
        $message = json_decode($message);
        $this->emit('message',['message' => $message ] );

        switch ($message->action)
        {
            case 'auth':
                if ($message->token == $this->TOKEN)
                {
                    $from->send(json_encode(['action' => 'auth' , 'message' => 'You are authenticated !' , 'result' => true ]));
                    $from->auth = true ;
                    $this->lastAuthClient = $from ;

                }
                else
                {
                    $from->auth = false ;
                    $from->send(json_encode(['action' => 'auth' , 'message' => 'You are not authenticated !' , 'result' => false ]));
                }
                break;
            default:
            {
                if (!$this->parseAuthMessage($from,$message))
                    $from->send(json_encode(['action' => 'error' , 'message' => 'Unknown action !' ]));
            }

        }

    }

    public function parseAuthMessage(ConnectionInterface $from, $message)
    {
        if ($from->auth == false)
        {
            $from->send(json_encode(['action' => 'notice' , 'message' => 'You are not authenticated !' ]));
            return false ;
        }

        switch ($message->action)
        {
            case 'loaded':
                $this->emit('loaded');
                break;
            case 'ping':
                $from->send(json_encode(['action' => 'pong']));
                break;
            default:
                return false ;
        }

        return true ;

    }

    public function onClose(ConnectionInterface $conn) {
        $conn->auth = false ;
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
//        var_dump('error');
        $conn->close();
    }

    public function nwjsEval($code)
    {
        $this->lastAuthClient->send(json_encode(['action' => 'eval', 'code' => $code]));
    }
}
