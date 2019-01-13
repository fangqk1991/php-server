<?php

namespace FC\Server;

class FCMessenger
{
    private $_redisDB;
    private $_sessionID;

    public function __construct($host, $port)
    {
        $this->_redisDB = new \Redis();
        $this->_redisDB->connect($host, $port);
    }

    private function _send_api($api)
    {
        return sprintf('fc:messenger:api:%s', $api);
    }

    private function _generate_session_id()
    {
        return sprintf('fc:messenger:session:id:%s', uniqid());
    }

    private function _inner_wait_for_message($api, $timeout=10)
    {
        $item = $this->_redisDB->blPop([$api], $timeout);
        if($item)
            return $item[1];
        return NULL;
    }

    public function wait_for_message($api, $timeout=10, $receipt=TRUE)
    {
        $uid = $this->_inner_wait_for_message($this->_send_api($api), $timeout);
        $content = $this->_redisDB->get($uid);
        $this->_sessionID = $uid;
        if($receipt)
            $this->send_receipt('OK');
        return $content;
    }

    public function send_receipt($content)
    {
        if($this->_sessionID)
        {
            $this->_redisDB->rPush($this->_send_api($this->_sessionID), $content);
            $this->_sessionID = NULL;
        }
    }

    public function send_message($api, $content, $waiting=True, $timeout=10)
    {
        $sessionID = $this->_generate_session_id();
        $this->_redisDB->set($sessionID, $content);
        $this->_redisDB->expire($sessionID, 60);
        $this->_redisDB->rPush($this->_send_api($api), $sessionID);
        if($waiting)
            return NULL;
        return $this->_inner_wait_for_message($this->_send_api($sessionID), $timeout);
    }
}
