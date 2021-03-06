# 简介
基于 redis 的应用间通信服务，PHP 版。

### 设计说明
* [基于 Redis 的应用间通信](https://fqk.io/app-to-app-communication/)
* [基于 Redis 的「服务器」实现](https://fqk.io/server-base-on-redis/)

### 其他版本
* [Python 版](https://github.com/fangqk1991/py-server)

### 依赖
* [redis](https://redis.io/)

### 使用
将 `php-server` 添加到 `composer.json`

```
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/fangqk1991/php-server"
    }
  ],
  ...
  ...
  "require": {
    "fang/php-server": "dev-master"
  }
}
```

```
composer install
```

### Messenger 示例
`receiver-demo.php`

```
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
```

`sender-demo.php`

```
require_once __DIR__ . '/../../vendor/autoload.php';

use FC\Server\FCMessenger;

$host = '127.0.0.1';
$port = 6379;

$times = 0;
$sender = new FCMessenger($host, $port);

$response = $sender->sendMessage('test', 'Hello.');
echo sprintf("[Client] Received response: %s\n", $response);

$response = $sender->sendMessage('test', 'Nice to meet you.');
echo sprintf("[Client] Received response: %s\n", $response);

$response = $sender->sendMessage('test', 'Bye.');
echo sprintf("[Client] Received response: %s\n", $response);
```

![](https://image.fangqk.com/2019-01-14/messenger-demo-php.jpg)

### Server 示例
`TestServer`

```
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
```

`server-demo.php`

```
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestServer.php';

$server = new TestServer();
$server->work();
```

`client-demo.php`

```
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
```

![](https://image.fangqk.com/2019-01-14/server-demo-php.jpg)