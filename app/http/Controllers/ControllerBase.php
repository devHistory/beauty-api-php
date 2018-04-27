<?php


namespace App\Http\Controllers;


use App\Providers\Components\AesTrait;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Exception;

class ControllerBase extends Controller
{

    use AesTrait;


    public $data;


    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
    }


    public function initialize()
    {
        // check argv
        $key = $this->session->get('key');
        $iv = base64_decode($this->request->getHeader('Xt-Iv'));
        $raw = base64_decode($this->request->getRawBody());
        if (!$iv || !$raw || !$key) {
            $output = json_encode([
                'code'    => 400,
                'message' => 'failure, missing argv'
            ]);
            exit($output);
        }

        // decrypt
        try {
            $decrypt = $this->decrypt($key, $iv, $raw);
        } catch (Exception $e) {
            $output = json_encode([
                'code'    => 400,
                'message' => 'failure, decrypt error'
            ]);
            exit($output);
        }
        parse_str($decrypt, $this->data);
    }


    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
    }

}
