<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestServer.php';

$client = new TestServer();

echo sprintf("[Client] start.\n---\n");
for($i = 0; $i < 5; ++$i)
{
    echo sprintf("[Client] requesting.. [%s]\n", $i);
    $response = $client->request(TEST_API, ['index' => $i]);
    echo sprintf("[Client] Received response: %s\n", $response);
    sleep(1);
}

echo sprintf("---\nClient end.\n");