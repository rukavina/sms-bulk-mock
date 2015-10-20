<?php

use Slim\Slim;
use Predis\Client as PredisClient;

CONST BULK_COUNTER = 'bulk_num';
CONST BULK_HASH = 'bulk_hash';

require __DIR__ . '/../vendor/autoload.php';
$config = require dirname(__DIR__) . '/config.php';

$predis = new PredisClient($config['redis_conn_arr']);

Slim::registerAutoloader();
$app = new Slim();

$app->get('/messages', function () use ($predis, $app) {
    $data = $predis->hgetall(BULK_HASH);
    $result = [];
    foreach ($data as $id => $message) {
        $messageArr = json_decode($message, true);
        $messageArr['id'] = $id;
        $result[] = $messageArr;
    }
    
    usort($result, function($a, $b){
        if ($a['id'] == $b['id']) {
            return 0;
        }
        return ($a['id'] < $b['id']) ? -1 : 1;        
    });
    
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setStatus(200);
    echo json_encode($result);
});

$app->get('/messages/:id', function ($id)  use ($predis, $app) {
    $data = $predis->hget(BULK_HASH, $id);
    if(!$data){
        $app->response->setStatus(404);
        return;
    }
    $result = json_decode($data, true);
    $result['id'] = $id;    
    
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setStatus(200);
    echo json_encode($result);    
});

$app->delete('/messages/:id', function ($id)  use ($predis, $app) {
    $data = $predis->hget(BULK_HASH, $id);
    if(!$data){
        $app->response->setStatus(404);
        return;
    }
    
    $predis->hdel(BULK_HASH, $id);
    $app->response->setStatus(204);
});

$app->delete('/messages', function () use ($predis, $app) {
    $data = $predis->hgetall(BULK_HASH);
    foreach ($data as $id => $message) {
        $predis->hdel(BULK_HASH, $id);
    }
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setStatus(204);
});

$app->run();


