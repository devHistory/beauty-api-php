<?php

namespace App\Http\Controllers\V1;


class InitController extends ControllerBase
{

    public function initialize()
    {
        $this->data = $this->dispatcher->getParam('_data');
    }


    // 检查更新&日志
    public function updateAction()
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

        // TODO :: logs
        // TODO :: 配置

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }

}
