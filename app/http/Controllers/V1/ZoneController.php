<?php

namespace App\Http\Controllers\V1;


use App\Http\Models\Location;
use App\Providers\Components\FilterTrait;

class ZoneController extends ControllerBase
{

    use FilterTrait;


    public function beforeExecuteRoute()
    {
        parent::beforeExecuteRoute();
    }


    // 圈子 - 热门
    public function hotAction()
    {
    }


    // 圈子 - 最新
    public function latestAction()
    {
    }


    // 圈子 - 在线
    public function onlineAction()
    {
    }


    // 圈子 - 附近
    public function nearbyAction()
    {
        $lng = $this->filter($this->data['lng'], 'float!');
        $lat = $this->filter($this->data['lat'], 'float!');
        $distance = $this->filter($this->data['distance'], 'int!', 1000);
        $gender = $this->filter($this->data['gender'], 'int!');
        if (!$lng || !$lat) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'error location'
            ]);
        }

        $locationModel = new Location();
        $locationModel->collection = 'accounts';
        $filter = null;
        if ($gender) {
            $filter = ['gender' => $gender];
        }
        $cursor = $locationModel->getGeoNear([$lng, $lat], $distance, $filter, ['limit' => 50]);
        $data = [];
        foreach ($cursor as $value) {
            foreach ($value->results as $person) {
                $data[] = [
                    'dis'      => (int)$person->dis,
                    'uid'      => $person->obj->_id,
                    'name'     => $person->obj->name,
                    'gender'   => $person->obj->gender,
                    'location' => $person->obj->location->coordinates,
                    'avatar'   => isset($person->obj->avatar) ? $person->obj->avatar : '',
                ];
            }
        }
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $data
        ]);
    }

}
