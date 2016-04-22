<?php

use Predis\Client as PredisClient;
use GuzzleHttp\Client as HttpClient;
use BulkMock\Utils as BulkUtils;

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

$messageId = BulkUtils::guid();
$smsParts = BulkUtils::getNumberOfSMSsegments($_REQUEST['text']);

ob_start();

//close http conn. and flush
header("HTTP/1.1 202 OK");
echo "OK $messageId $smsParts";

header('Connection: close');
header('Content-Length: '.ob_get_length());
ob_end_flush();
ob_flush();
flush();

//proceed with dlr
if(!isset($_REQUEST['dlr-url'])){
    return;
}

$dlrUrl = $_REQUEST['dlr-url'];

$httpClient = new HttpClient();

$dlrVars = array(
    '%U' => $messageId,	//Message ID	Message ID as returned when message is sent, see here
    '%d' => 1,	//DLR Event, see here	1
    '%s' => $_REQUEST['sender'],	//Sender	
    '%r' => $_REQUEST['receiver'],	//Receiver	
    '%e' => '',	//Error code	26
    '%E' => '',	//Error description	Unknown subscriber
    '%A' => $_REQUEST['user'],	//Account name used for submission.	YOUR_USERNAME
    '%p' => 0,	//Part number [0 to total_parts-1]	1
    '%P' => $smsParts,	//Total number of parts	3
);

$dlrUrl = strtr($dlrUrl, $dlrVars);

$response = $httpClient->get($dlrUrl);
