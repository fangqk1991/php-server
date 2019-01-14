<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestServer.php';

$server = new TestServer();
$server->work();
