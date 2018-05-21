<?php

namespace App\Http\Controllers\V1;


use App\Http\Models\Location;
use App\Providers\Components\FilterTrait;

class InitController extends ControllerBase
{

    use FilterTrait;


    private $locationModel;


    public function beforeExecuteRoute()
    {
        parent::beforeExecuteRoute();
        $this->locationModel = new Location();
        $this->locationModel->collection = 'accounts';
    }


    public function syncAction()
    {
        $data = [
            'uuid'    => $this->filter($this->data['uuid'], 'string'),
            'adid'    => $this->filter($this->data['adid'], 'string'),
            'lng'     => $this->filter($this->data['lng'], 'float!'),       // -180 to 180
            'lat'     => $this->filter($this->data['lat'], 'float!'),       // -90 to 90
            'os'      => $this->filter($this->data['os'], 'int!'),          // [1:ios, 2:android]
            'model'   => $this->filter($this->data['model'], 'string'),     // iphone6s Plus
            'channel' => $this->filter($this->data['channel'], 'string'),
            'ip'      => $this->request->getClientAddress(),
            'login'   => time(),
        ];

        if (!empty($data['lng']) && !empty($data['lat'])) {
            $this->locationModel->set($this->uid, [$data['lng'], $data['lat']]);
        }

        // TODO :: logs
        // TODO :: 安全设备认证

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }

}
