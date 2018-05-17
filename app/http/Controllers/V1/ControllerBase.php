<?php


namespace App\Http\Controllers\V1;


use App\Providers\Components\AesTrait;
use Phalcon\Mvc\Controller;
use Exception;

class ControllerBase extends Controller
{

    use AesTrait;


    public $uid;


    public $data;


    private $_aesKey;


    private $_iv;


    public function beforeExecuteRoute()
    {
        $this->checkSid();
        $this->checkUid();
        $this->prepareData();
    }


    protected function checkSid()
    {
        $sid = $this->request->getHeader('Xt-Sid');
        $this->_iv = base64_decode($this->request->getHeader('Xt-Iv'));
        if (!$sid) {
            return $this->dispatcher->forward([
                'controller' => 'Default',
                'action'     => 'apiException',
                'params'     => ['code' => 401, 'message' => 'missing argv: sid']
            ]);
        }
        if (!$this->_iv) {
            return $this->dispatcher->forward([
                'controller' => 'Default',
                'action'     => 'apiException',
                'params'     => ['code' => 412, 'message' => 'missing argv: iv']
            ]);
        }

        $this->_aesKey = $this->cache->get('_sid|' . $sid);
        if (!$this->_aesKey) {
            return $this->dispatcher->forward([
                'controller' => 'Default',
                'action'     => 'apiException',
                'params'     => ['code' => 408, 'message' => 'session timeout']
            ]);
        }
    }


    protected function prepareData()
    {
        $raw = base64_decode($this->request->getRawBody());

        // decrypt
        try {
            $decrypt = $this->decrypt($this->_aesKey, $this->_iv, $raw);
        } catch (Exception $e) {
            return $this->dispatcher->forward([
                'controller' => 'Default',
                'action'     => 'apiException',
                'params'     => ['code' => 417, 'message' => 'decrypt failed']
            ]);
        }
        parse_str($decrypt, $this->data);
    }


    private function checkUid()
    {
        $token = $this->request->getHeader('Xt-Token');
        if (!$token) {
            return $this->dispatcher->forward([
                'controller' => 'Default',
                'action'     => 'apiException',
                'params'     => ['code' => 401, 'message' => 'missing argv: token']
            ]);
        }
        if (!$data = $this->support->verifyToken($token)) {
            return $this->dispatcher->forward([
                'controller' => 'Default',
                'action'     => 'apiException',
                'params'     => ['code' => 401, 'message' => 'token error']
            ]);
        }
        $this->uid = $data->dat->uid;
    }


}
