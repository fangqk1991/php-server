<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FC\Server\FCMessenger;


$host = '127.0.0.1';
$port = 6379;

$times = 0;
$sender = new FCMessenger($host, $port);

$response = $sender->send_message('test', 'Hello.');
echo sprintf("[Client] Received response: %s\n", $response);

$response = $sender->send_message('test', 'Nice to meet you.');
echo sprintf("[Client] Received response: %s\n", $response);

$response = $sender->send_message('test', 'Bye.');
echo sprintf("[Client] Received response: %s\n", $response);
