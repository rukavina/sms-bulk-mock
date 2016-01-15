<?php

return array(
    'redis_conn_url'    => 'tcp://127.0.0.1:6379',
    'redis_conn_arr'    => array(
        "scheme" => "tcp",
        "host" => "127.0.0.1",
        "port" => 6379        
    ),
    'client'    => array(
        'log_file'  => 'data/log/client.log',
    ),
    'bulk_server' =>array(
        'port' => 8080
    ),    
);

