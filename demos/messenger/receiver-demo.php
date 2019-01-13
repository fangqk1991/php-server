<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FC\Server\FCMessenger;


$host = '127.0.0.1';
$port = 6379;

$times = 0;
$receiver = new FCMessenger($host, $port);

echo "[Receiver] Waiting message...\n";
while(true)
{
    $msg = $receiver->wait_for_message('test', 0, FALSE);
    $times += 1;

    echo sprintf("[Receiver] Received message: %s\n", $msg);
    $receiver->send_receipt(sprintf('Times: %d', $times));

    if($msg === 'Bye.')
    {
        break;
    }
}

echo sprintf("[Receiver] Close\n");
