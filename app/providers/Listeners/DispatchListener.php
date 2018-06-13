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
 */

namespace App\Providers\Listeners;


use App\Providers\Components\AesTrait;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI;
use Exception;

class DispatchListener
{

    use AesTrait;


    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        if ($dispatcher->getControllerName() == 'V1\Access') {
            return true;
        }
        $di = DI::getDefault();
        $mode = $di['request']->getHeader('Xt-Mode');
        $sid = $di['request']->getHeader('Xt-Sid');
        $iv = base64_decode($di['request']->getHeader('Xt-Iv'));

        // check
        if (!$sid) {
            $this->output(['code' => 401, 'message' => 'missing argv: sid']);
        }
        if (!$iv) {
            $this->output(['code' => 412, 'message' => 'missing argv: iv']);
        }
        $session = $di['cache']->hGetAll('_sid|' . $sid);
        if (!$session) {
            $this->output(['code' => 408, 'message' => 'session timeout']);
        }

        // default mode
        if (empty($mode)) {
            $mode = 'raw';
        }

        // decrypt
        try {
            if ($mode == 'base64') {
                $decrypt = $this->decrypt($session['aes'], $iv, base64_decode($di['request']->getRawBody()));
            }
            else {
                $decrypt = $this->decrypt($session['aes'], $iv, $di['request']->getRawBody());
            }
            parse_str($decrypt, $data);
            $dispatcher->setParam('_data', $data);
            if (isset($session['uid'])) {
                $dispatcher->setParam('_uid', $session['uid']);
            }
        } catch (Exception $e) {
            $this->output(['code' => 417, 'message' => 'decrypt failed']);
        }

        unset($data, $decrypt);
    }


    private function output($data)
    {
        exit(json_encode($data));
    }


}
