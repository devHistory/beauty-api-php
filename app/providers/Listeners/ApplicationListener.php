<?php

/**
 * Class ApplicationListener
 * @package App\Providers\Listeners
 * @link https://docs.phalconphp.com/zh/3.3/events#list
 *
 * public function boot(Event $event, Application $app)
 * public function beforeStartModule(Event $event, Application $app)
 * public function afterStartModule(Event $event, Application $app)
 * public function beforeHandleRequest(Event $event, Application $app)
 * public function afterHandleRequest(Event $event, Application $app)
 * public function viewRender(Event $event, Application $app)
 * public function beforeSendResponse(Event $event, Application $app)
 */

namespace App\Providers\Listeners;


use App\Providers\Components\AesTrait;
use Phalcon\Events\Event;
use Phalcon\Mvc\Application;

class ApplicationListener
{

    use AesTrait;


    public function beforeSendResponse(Event $event, Application $app)
    {
        $mode = $app->request->getHeader('Xt-Mode');
        $aesKey = $app->cache->hGet('_sid|' . $app->request->getHeader('Xt-Sid'), 'aes');
        if (!$aesKey) {
            return true;
        }

        $payload = $app->response->getContent();
        $data = $this->encrypt($aesKey, $payload);
        $app->response->setHeader('Xt-Iv', base64_encode($data['0']));
        if ($mode == 'base64') {
            $app->response->setContent(base64_encode($data['1']));
        }
        else {
            $app->response->setContent($data['1']);
        }
    }

}
