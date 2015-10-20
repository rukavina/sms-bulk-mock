<?php

namespace BulkMock;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use GuzzleHttp\Client as HttpClient;

class BulkMockApp implements MessageComponentInterface {

    protected $clients;
    
    /**
     *
     * @var HttpClient 
     */
    protected $httpClient;
    
    protected $config;

    public function __construct(array $config) {
        $this->clients = new \SplObjectStorage;
        $this->httpClient = new HttpClient();
        $this->config = $config;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function broadcast($messageType, array $messageData, $from = null)
    {
        $message = json_encode(array(
            'type'  => $messageType,
            'data'  => $messageData
        ));
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($message);
            }
        }        
    }
    
    public function sendError($message)
    {
        $this->broadcast('error', array('message' => $message));
    }
    
    public function processBulkMsg(array $parameters)
    {
        $this->broadcast('bulk_msg', $parameters);
    }   

    public function onMessage(ConnectionInterface $from, $msg) {
        $message = json_decode($msg, true);
        switch ($message['type']) {
            case 'mo':
                $this->postMo($message['data']);
                break;

            default:
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

}
