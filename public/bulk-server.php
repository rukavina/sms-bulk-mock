<?php

use Predis\Client as PredisClient;
use GuzzleHttp\Client as HttpClient;
use BulkMock\Utils as BulkUtils;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

CONST BULK_COUNTER = 'bulk_num';
CONST BULK_HASH = 'bulk_hash';

require dirname(__DIR__) . '/vendor/autoload.php';
$config = require dirname(__DIR__) . '/config.php';

// create a log channel
$log = new Logger('serverlog');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/' . $config['client']['log_file']));

//get params
if(!$_REQUEST['sender'] || !$_REQUEST['receiver'] || !$_REQUEST['text']){
    $log->error('Invalid request');
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

$log->info("Valid request and replied: OK $messageId $smsParts");

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

$log->info("Sending DRL to url: [$dlrUrl]");

$errorMap = [
    0 => 'No error',
    1 => 'Unknown subscriber',
    9 => 'Illegal subscriber',
    11 => 'Teleservice not provisioned',
    13 => 'Call barred',
    15 => 'CUG reject',
    19 => 'No SMS support in MS',
    20 => 'Error in MS',
    21 => 'Facility not supported',
    22 => 'Memory capacity exceeded',
    29 => 'Absent subscriber',
    30 => 'MS busy for MT SMS',
    36 => 'Network/Protocol failure',
    44 => 'Illegal equipment',
    60 => 'No paging response',
    61 => 'GMSC congestion',
    63 => 'HLR timeout',
    64 => 'MSC/SGSN_timeout',
    70 => 'SMRSE/TCP error',
    72 => 'MT congestion',
    75 => 'GPRS suspended',
    80 => 'No paging response via MSC',
    81 => 'IMSI detached',
    82 => 'Roaming restriction',
    83 => 'Deregistered in HLR for GSM',
    84 => 'Purged for GSM',
    85 => 'No paging response via SGSN',
    86 => 'GPRS detached',
    87 => 'Deregistered in HLR for GPRS',
    88 => 'The MS purged for GPRS',
    89 => 'Unidentified subscriber via MSC',
    90 => 'Unidentified subscriber via SGSN',
    112 => 'Originator missing credit on prepaid account',
    113 => 'Destination missing credit on prepaid account',
    114 => 'Error in prepaid system',
    500 => 'Other error',
    990 => 'HLR failure',
    991 => 'Rejected by message text filter',
    992 => 'Ported numbers not supported on destination',
    993 => 'Blacklisted sender',
    994 => 'No credit',
    995 => 'Undeliverable',
    996 => 'Validity expired',
    997 => 'Blacklisted receiver',
    998 => 'No route',
    999 => 'Repeated submission (possible looping)',    
];

$errorCode = isset($config['bulk_server']['dlr_error_code'])? $config['bulk_server']['dlr_error_code']: 0;
$errorDesc = isset($errorMap[$errorCode])? $errorMap[$errorCode]: '';

$httpClient = new HttpClient();

$dlrVars = array(
    '%U' => $messageId,	//Message ID	Message ID as returned when message is sent, see here
    '%d' => isset($config['bulk_server']['dlr_event'])? $config['bulk_server']['dlr_event']: 1, //DLR Event, see here	1
    '%s' => $_REQUEST['sender'],	//Sender	
    '%r' => $_REQUEST['receiver'],	//Receiver	
    '%e' => $errorCode,	//Error code	26
    '%E' => $errorDesc,	//Error description	Unknown subscriber
    '%A' => $_REQUEST['user'],	//Account name used for submission.	YOUR_USERNAME
    '%p' => 0,	//Part number [0 to total_parts-1]	1
    '%P' => $smsParts,	//Total number of parts	3
);

$dlrUrl = strtr($dlrUrl, $dlrVars);

$response = $httpClient->get($dlrUrl);
