<?php


namespace App\Http\Controllers;


use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Zend\Crypt\PublicKey\Rsa;
use Exception;

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


    public function secretsAction()
    {
        // get data
        $encrypt = $this->request->getRawBody();
        if (!$encrypt) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failure, no data',
            ]);
        }

        // decrypt
        $rsa = Rsa::factory([
            'private_key' => CONFIG_DIR . '/rsa.pem',
        ]);
        try {
            $decrypt = $rsa->decrypt(base64_decode($encrypt));
        } catch (Exception $e) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failure, decrypt error',
            ]);
        }

        // set aes key
        $this->session->set('key', $decrypt);
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
        ]);
    }

}
