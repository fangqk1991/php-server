<?php

namespace FC\Server;

use Redis;

class FCMessenger
{
    private $_redisDB;
    private $_sessionID;
    public $defaultSessionTime;

    public function __construct($host, $port)
    {
        $this->_redisDB = new Redis();
        $this->_redisDB->connect($host, $port);
        $this->_redisDB->setOption(Redis::OPT_READ_TIMEOUT, -1);
    }

    private function _sendAPI($api)
    {
        return sprintf('fc:messenger:api:%s', $api);
    }

    private function _generateSessionID()
    {
        return sprintf('fc:messenger:session:id:%s', uniqid());
    }

    private function _innerWaitForMessage($api, $timeout=10)
    {
        $item = $this->_redisDB->blPop([$api], $timeout);
        if($item)
            return $item[1];
        return NULL;
    }

    public function waitForMessage($api, $timeout=10, $receipt=TRUE)
    {
        $uid = $this->_innerWaitForMessage($this->_sendAPI($api), $timeout);
        $content = $this->_redisDB->get($uid);
        $this->_sessionID = $uid;
        if($receipt)
            $this->sendReceipt('OK');
        return $content;
    }

    public function sendReceipt($content)
    {
        if($this->_sessionID)
        {
            $this->_redisDB->rPush($this->_sendAPI($this->_sessionID), $content);
            $this->_sessionID = NULL;
        }
    }

    public function sendMessage($api, $content, $waiting=True, $timeout=10)
    {
        $sessionID = $this->_generateSessionID();
        $this->_redisDB->set($sessionID, $content);
        $this->_redisDB->expire($sessionID, $this->defaultSessionTime);
        $this->_redisDB->rPush($this->_sendAPI($api), $sessionID);
        if(!$waiting)
            return NULL;
        return $this->_innerWaitForMessage($this->_sendAPI($sessionID), $timeout);
    }
}
