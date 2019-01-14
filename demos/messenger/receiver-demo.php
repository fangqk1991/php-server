<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FC\Server\FCMessenger;

$host = '127.0.0.1';
$port = 6379;

$receiver = new FCMessenger($host, $port);

echo "[Receiver] Waiting message...\n";
while(TRUE)
{
    $msg = $receiver->waitForMessage('test', 0, FALSE);
    echo sprintf("[Receiver] Received message: %s\n", $msg);
    $receiver->sendReceipt(sprintf('You said |%s|', $msg));
    if($msg === 'Bye.')
        break;
}

echo sprintf("[Receiver] Close\n");
