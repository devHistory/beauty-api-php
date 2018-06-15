<?php

/**
 * Class DispatchListener
 * @package App\Providers\Listeners
 * @link https://docs.phalconphp.com/zh/3.3/events#list
 * @link https://docs.phalconphp.com/zh/3.3/dispatcher#dispatch-loop-events
 *
 * public function beforeDispatchLoop()
 * public function beforeDispatch()
 * public function beforeExecuteRoute()
 * public function afterInitialize()
 * public function afterExecuteRoute()
 * public function afterDispatch()
 * public function afterDispatchLoop()
 * public function beforeException()
 * public function beforeForward()
 * public function beforeNotFoundAction()
 *
 *
 * 签名包含4部分
 * 1. Key       : 客户端生成的密钥
 * 2. URI       : 中文等特殊字符需要UrlEncode处理
 * 3. HEADER    : 所有Xt-开头的headers拼接成字符串k1=v1&k2=v2的形式
 * 4. BodyMd5   : Body部分的Md5值
 *
 * 拼接 : URI . "\n" . HEADER . "\n" . BodyMd5
 * 哈希 : hash_hmac('sha1', SIGN_STRING, KEY)
 *
 */

namespace App\Providers\Listeners;


use App\Providers\Components\AesTrait;
use App\Providers\Exception\RequestException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI;

class DispatchListener
{

    private $session;


    use AesTrait;


    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        if ($dispatcher->getControllerName() == 'V1\Access') {
            return true;
        }

        $this->checkTimeout();

        $this->checkSign();

        $this->decryptData($dispatcher);
    }


    private function checkTimeout()
    {
        $time = DI::getDefault()['request']->getHeader('Xt-Time');
        if (abs($time - time()) > 300) {
            throw new  RequestException('request timeout: Xt-Time', 400);
        }
    }


    private function checkSign()
    {
        $di = DI::getDefault();
        $headers = $di['request']->getHeaders();
        $body = $di['request']->getRawBody();
        $sign = $di['request']->getHeader('Xt-Sign');
        $uri = $di['request']->getURI();
        unset($headers['Xt-Sign']);
        ksort($headers);
        $headerString = '';
        foreach ($headers as $key => $value) {
            if (strpos($key, 'Xt-') === 0) {
                $headerString .= '&' . $key . '=' . $value;
            }
        }
        $signString = $uri . "\n" . ltrim($headerString, '&') . "\n" . md5($body);
        $session = $this->getSession();
        if ($sign != hash_hmac('sha1', $signString, $session['aes'])) {
            throw new  RequestException('request sign error', 400);
        }
    }


    private function decryptData(Dispatcher $dispatcher)
    {
        $di = DI::getDefault();
        $mode = $di['request']->getHeader('Xt-Mode');
        $iv = base64_decode($di['request']->getHeader('Xt-Iv'));
        if (!$iv) {
            throw new  RequestException('missing argv: iv', 412);
        }

        // decrypt
        if (empty($mode)) {
            $mode = 'raw';
        }
        try {
            if ($mode == 'base64') {
                $decrypt = $this->decrypt($this->getSession()['aes'], $iv, base64_decode($di['request']->getRawBody()));
            }
            else {
                $decrypt = $this->decrypt($this->getSession()['aes'], $iv, $di['request']->getRawBody());
            }
            parse_str($decrypt, $data);
            $dispatcher->setParam('_data', $data);
            if (isset($this->getSession()['uid'])) {
                $dispatcher->setParam('_uid', $this->getSession()['uid']);
            }
        } catch (RequestException $e) {
            throw new  RequestException('decrypt failed', 417);
        }
        unset($data, $decrypt);
    }


    private function getSession()
    {
        if ($this->session) {
            return $this->session;
        }
        $di = DI::getDefault();
        $sid = $di['request']->getHeader('Xt-Sid');
        if (!$sid) {
            throw new  RequestException('missing argv: sid', 401);
        }
        $this->session = $di['cache']->hGetAll('_sid|' . $sid);
        if (!$this->session) {
            throw new  RequestException('session timeout', 408);
        }
        return $this->session;
    }


}
