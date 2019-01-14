<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FC\Server\FCMessenger;
use FC\Server\FCServer;

define('HOST', '127.0.0.1');
define('PORT', 6379);
define('TEST_API', 'some/api');

class TestServer extends FCServer
{
    public function __construct()
    {
        $this->init(HOST, PORT, 'SOME-SERVER-NAME');
    }

    public function fc_test(FCMessenger $context, $params)
    {
        $index = $params['index'];
        echo sprintf("[Server] Received message [%s]\n", $index);
        $this->answer($context, sprintf('Welcome. [%s]', $index));
    }

    protected function routerMap()
    {
        return [
            TEST_API => 'fc_test',
        ];
    }
}
