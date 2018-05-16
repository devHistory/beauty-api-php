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


    public function initialize()
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
            $output = ['code' => 401, 'message' => 'missing argv: sid'];
            $this->response->setJsonContent($output)->send();
            exit();
        }
        if (!$this->_iv) {
            $output = ['code' => 412, 'message' => 'missing argv: iv'];
            $this->response->setJsonContent($output)->send();
            exit();
        }

        $this->_aesKey = $this->cache->get('_sid|' . $sid);
        if (!$this->_aesKey) {
            $output = [
                'code'    => 408,
                'message' => 'session timeout'
            ];
            $this->response->setJsonContent($output)->send();
            exit();
        }
    }


    protected function prepareData()
    {
        $raw = base64_decode($this->request->getRawBody());

        // decrypt
        try {
            $decrypt = $this->decrypt($this->_aesKey, $this->_iv, $raw);
        } catch (Exception $e) {
            $output = [
                'code'    => 417,
                'message' => 'decrypt failed'
            ];
            $this->response->setJsonContent($output)->send();
            exit();
        }
        parse_str($decrypt, $this->data);
    }


    private function checkUid()
    {
        $token = $this->request->getHeader('Xt-Token');
        if (!$token) {
            $output = ['code' => 401, 'message' => 'missing argv: token'];
            $this->response->setJsonContent($output)->send();
            exit();
        }
        if (!$data = $this->support->verifyToken($token)) {
            $output = ['code' => 401, 'message' => 'token error'];
            $this->response->setJsonContent($output)->send();
            exit();
        }
        $this->uid = $data->dat->uid;
    }


}
