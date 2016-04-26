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
        'test_url'  => 'http://127.0.0.1/work/sms-bulk-mock/public//bulk-server.php?type=text&user=testuser&password=testpass&sender=Bulk+Test&receiver=%2B4178123456&dcs=GSM&text=This+is+test+message+from-cli&dlr-mask=19'
    ),
    'bulk_server' =>array(
        'port' => 8080,
        'dlr_event' => 1,
        //'dlr_error_code' => 22 //set it you want to test negative dlr reply        
    ),    
);

