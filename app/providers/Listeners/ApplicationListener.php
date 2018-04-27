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


    public function boot(Event $event, Application $app)
    {
        // check time
        $timestamp = $app->request->getHeader('Xt-Timestamp');
        if (!$timestamp || abs(time() - $timestamp) > 300) {
            $output = json_encode([
                'code'    => 400,
                'message' => 'failure, timeout'
            ]);
            exit($output);
        }
    }

    public function beforeSendResponse(Event $event, Application $app)
    {
        $payload = $app->response->getContent();
        $data = $this->encrypt($app->session->get('key'), $payload);
        $app->response->setHeader('Xt-Iv', base64_encode($data['0']));
        $app->response->setContent(base64_encode($data['1']));
    }

}
