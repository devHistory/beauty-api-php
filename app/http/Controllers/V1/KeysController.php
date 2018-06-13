<?php


namespace App\Http\Controllers\V1;


use Phalcon\Mvc\Controller;

class KeysController extends Controller
{

    public function initialize()
    {
        /**
         * Removes all events from the EventsManager
         * @see https://docs.phalconphp.com/en/3.3/api/Phalcon_Events_Manager
         */
        $this->eventsManager->detachAll('application');
    }


    public function publicAction()
    {
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => file_get_contents(CONFIG_DIR . '/rsa.pub'),
        ]);
    }

}
