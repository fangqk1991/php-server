<?php

namespace FC\Server;

use Exception;

abstract class FCServer
{
    private $_host;
    private $_port;
    private $_domain;

    protected $asyncMode = FALSE;
    protected $api;

    /**
     * @return array
     */
    abstract protected function routerMap();

    public function init($host, $port, $domain)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_domain = $domain;
    }

    public function request($reqApi, $params=[], $waiting=TRUE, $timeout=10)
    {
        if($this->asyncMode)
        {
            $waiting = FALSE;
        }

        $params['fc_server_request_api'] = $reqApi;

        $messenger = new FCMessenger($this->_host, $this->_port);
        if(!$waiting)
        {
            $messenger->sendMessage($this->_domain, json_encode($params), FALSE);
            return NULL;
        }

        $response = $messenger->sendMessage($this->_domain, json_encode($params), TRUE, $timeout);
        $response = json_decode($response, TRUE);
        if(isset($response['data']))
        {
            return $response['data'];
        }

        $error = $response['error'];
        throw new FCException($error['msg']);
    }

    public function listen()
    {
        $context = new FCMessenger($this->_host, $this->_port);
        $content = $context->waitForMessage($this->_domain, 0, FALSE);
        $params = json_decode($content, TRUE);
        if(!is_array($params) || !isset($params['fc_server_request_api']))
        {
            return [$context, NULL, NULL];
        }

        $reqApi = $params['fc_server_request_api'];
        unset($params['fc_server_request_api']);
        return [$context, $reqApi, $params];
    }

    public function answer(FCMessenger $context, $data)
    {
        $context->sendReceipt(json_encode(['data' => $data]));
    }

    public function throwError(FCMessenger $context, $msg)
    {
        $error = ['msg' => $msg, 'code' => -1];
        $context->sendReceipt(json_encode(['error' => $error]));
    }

    public function work()
    {
        $apiMap = $this->routerMap();

        $methods = get_class_methods($this);
        $map = [];
        foreach($methods as $method)
        {
            $map[$method] = 1;
        }
        foreach ($apiMap as $api => $method)
        {
            if(!isset($map[$method]))
            {
                throw new FCException(sprintf('%s not implements', $method));
            }
        }

        while(TRUE)
        {
            list($context, $reqApi, $params) = $this->listen();
            if(!$reqApi)
            {
                $this->throwError($context, 'req api missing');
                continue ;
            }

            if(!isset($apiMap[$reqApi]))
            {
                $this->throwError($context, 'req api not in api map');
                continue ;
            }

            $method = $apiMap[$reqApi];

            try
            {
                if($this->asyncMode)
                {
                    $this->answer($context, 'OK');
                }

                $this->$method($context, $params);
            }
            catch (Exception $e)
            {
                $this->throwError($context, $e->getMessage());
            }
        }
    }
}