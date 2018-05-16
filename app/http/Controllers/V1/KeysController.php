<?php


namespace App\Http\Controllers\V1;


use Phalcon\Mvc\Controller;
use Zend\Crypt\PublicKey\Rsa;
use MongoDB\BSON\ObjectId;
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


    /**
     * Set AES key and get a SID
     */
    public function secretsAction()
    {
        // get data
        $encrypt = $this->request->getRawBody();
        if (!$encrypt) {
            return $this->response->setJsonContent([
                'code'    => 406,
                'message' => 'failed, no data',
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
                'code'    => 406,
                'message' => 'failed, can not decrypt',
            ]);
        }

        // set aes key
        $sid = new ObjectId();
        $timeout = 86400 * 14;
        $this->cache->set('_sid|' . $sid->__toString(), $decrypt, $timeout);
        $output = [
            'code'    => 200,
            'message' => 'success',
            'payload' => [
                'sid'     => $sid->__toString(),
                'timeout' => $timeout,
            ]
        ];
        return $this->response->setJsonContent($output);
    }

}
