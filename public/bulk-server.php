<?php

use Predis\Client as PredisClient;

CONST BULK_COUNTER = 'bulk_num';
CONST BULK_HASH = 'bulk_hash';

require dirname(__DIR__) . '/vendor/autoload.php';
$config = require dirname(__DIR__) . '/config.php';

//get params
if(!$_REQUEST['sender'] || !$_REQUEST['receiver'] || !$_REQUEST['text']){
    header("HTTP/1.1 420 OK");
    die("ERR 110");
}

$client = new PredisClient($config['redis_conn_url']);

$bulkNum  = (int)$client->get(BULK_COUNTER);
$bulkNum ++;
$client->set(BULK_COUNTER, $bulkNum);

$message = json_encode($_REQUEST);

//store message
$client->hset(BULK_HASH, $bulkNum, $message);

$client->publish('bulkmocksend', $message);

$client->quit();

header("HTTP/1.1 202 OK");
die("OK 111 1");
