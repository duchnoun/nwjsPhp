<?php

use Castor\Attribute\AsTask;
use React\EventLoop\Loop ;
use function Castor\io ;
include 'websocketServer.php';

#[AsTask(description: 'Main !')]
function main(): void
{
    $loop = Loop::get();
    $TOKEN = md5(uniqid());

    $nwjsServer = new NwJsServerWS($TOKEN,$loop) ;
    $nwjsServer->on('message',function ($message)
    {
        io()->note('Message received : ' . json_encode($message) );
    }) ;

    $loop->addPeriodicTimer(2, function () use ($nwjsServer) {
        echo "2 second passed\n";
        $code = <<<JS
                            console.log('Hello from PHP');
                            console.log(nw);
            
                            var your_menu = new nw.Menu({ type: 'menubar' });
                             var submenu = new nw.Menu();
                             submenu.append(new nw.MenuItem({ label: 'Item A' }));
                             submenu.append(new nw.MenuItem({ label: 'Item B' }));
                             //
                             your_menu.append(new nw.MenuItem({
                                 label: 'First Menu',
                                 submenu: submenu
                             }));
                             nw.Window.get().menu = your_menu;
            JS;
        $nwjsServer->nwjsEval($code);
    });

    $nwjsServer->run();
}
