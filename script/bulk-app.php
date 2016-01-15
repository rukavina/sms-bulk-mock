<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use BulkMock\BulkMockApp;
use Predis\Async\Client as PredisClient;

require dirname(__DIR__) . '/vendor/autoload.php';
$config = require dirname(__DIR__) . '/config.php';

$mockApp = new BulkMockApp($config);
$server = IoServer::factory(new HttpServer(new WsServer($mockApp)), $config['bulk_server']['port']);

$client = new PredisClient($config['redis_conn_url'], $server->loop);

$client->connect(function ($client) use ($mockApp){
    echo "Connected to Redis, now listening for incoming messages...\n";

    $client->pubSubLoop('bulkmocksend', function ($event) use ($mockApp){
        echo "Received new Bulk Msg `{$event->payload}` from {$event->channel}.\n";
        $mockApp->processBulkMsg(json_decode($event->payload, true));
    });
});

$server->run();
