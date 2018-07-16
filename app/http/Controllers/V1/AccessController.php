<?php

namespace App\Http\Controllers\V1;


use Phalcon\Mvc\Controller;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Math\Rand;
use Exception;

class AccessController extends Controller
{

    /**
     * Set AES key and get a SID
     */
    public function sessionAction()
    {
        // get data
        $mode = $this->request->getHeader('Xt-Mode');
        $raw = $this->request->getRawBody();
        if (!$raw) {
            return $this->response->setJsonContent([
                'code'    => 406,
                'message' => 'failed, no data',
            ]);
        }

        if ($mode == 'plaintext') {
            $keyData = $raw;
        }
        else {
            $rsa = Rsa::factory([
                'private_key' => CONFIG_DIR . '/rsa.pem',
            ]);
            try {
                // Rsa::MODE_AUTO 模式自动处理base64
                $keyData = $rsa->decrypt($raw);
            } catch (Exception $e) {
                return $this->response->setJsonContent([
                    'code'    => 406,
                    'message' => 'failed, can not decrypt',
                ]);
            }
        }

        // check AES key
        if (strlen($keyData) < 8 || strlen($keyData) > 64) {
            return $this->response->setJsonContent([
                'code'    => 406,
                'message' => 'failed, error key length',
            ]);
        }

        // set aes key
        $timeout = 86400 * 14;
        $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $sid = Rand::getString(32, $charList);
        $k = '_sid|' . $sid;
        $this->cache->hSet($k, 'aes', $keyData);
        $this->cache->expire($k, $timeout);
        $output = [
            'code'    => 200,
            'message' => 'success',
            'payload' => [
                'sid'     => $sid,
                'timeout' => $timeout,
            ]
        ];
        return $this->response->setJsonContent($output);
    }

}
