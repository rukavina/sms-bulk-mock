<?php

use GuzzleHttp\Client as HttpClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require dirname(__DIR__) . '/vendor/autoload.php';
$config = require dirname(__DIR__) . '/config.php';

// create a log channel
$log = new Logger('clientlog');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/' . $config['client']['log_file']));

$log->addInfo('Sending test bulk message',[]);

//send bulk
$httpClient = new HttpClient();

$log->info('Sending bulk message to : '. $config['client']['test_url']);

$response = $httpClient->post($config['client']['test_url'], []);

$log->addInfo('Received Bulk REPLY: ' . $response->getBody());
