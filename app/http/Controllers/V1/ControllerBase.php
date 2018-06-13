<?php


namespace App\Http\Controllers\V1;


use App\Providers\Components\UtilsTrait;
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{

    public $uid;


    public $data;


    use UtilsTrait;


    public function initialize()
    {
        $this->data = $this->dispatcher->getParam('_data');
        $this->uid = $this->dispatcher->getParam('_uid');

        if (!$this->uid) {
            exit(json_encode(['code' => 400, 'message' => 'account is not login']));
        }
    }

}
