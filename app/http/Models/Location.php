<?php

namespace App\Http\Models;


use Phalcon\Mvc\Model;

class Location extends Model
{

    public $field = 'location';


    public $collection;


    /**
     * @link https://docs.mongodb.com/manual/reference/geojson/
     * @param string $id
     * @param array $coordinates [lng,lat]
     * @return mixed
     */
    public function set($id = '', $coordinates = [])
    {
        $db = $this->di['config']['database']['mongodb']['database'];
        $data = [
            $this->field => [
                'type'        => 'Point',
                'coordinates' => $coordinates
            ]
        ];
        return $this->di['mongodb']->$db->{$this->collection}->updateOne(
            ['_id' => $id],
            ['$set' => $data + ['mTime' => time()]]
        );
    }


    /**
     * 附近数据 [距离单位: near GeoJSON格式单位为米，坐标对时单位为弧度]
     * @link https://docs.mongodb.com/php-library/v1.3/tutorial/commands/index.html
     * @link https://docs.mongodb.com/manual/reference/command/geoNear/#dbcmd.geoNear
     * @param array $coordinates [lng,lat]
     * @param int $distance
     * @param array $option [limit]
     * @return resource $cursor
     */
    public function get($coordinates = [], $distance = 1000, $option = [])
    {
        $db = $this->di['config']['database']['mongodb']['database'];
        $query = [
            'geoNear'     => $this->collection,
            'near'        => [
                'type'        => 'Point',
                'coordinates' => $coordinates,
            ],
            'spherical'   => 'true',
            'minDistance' => 0,
            'maxDistance' => $distance
        ];
        $query['num'] = empty($option['limit']) ? 100 : $option['limit'];
        $cursor = $this->di['mongodb']->$db->command($query);
        return $cursor;
    }


    /**
     * 附近数据 [不排序]
     * The equatorial radius of the Earth is approximately 3,963.2 miles or 6,378.1 kilometers.
     * @link https://docs.mongodb.com/manual/geospatial-queries/
     * @link https://docs.mongodb.com/manual/reference/operator/query/geoWithin/
     * @link https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-find/#phpmethod.MongoDB%5CCollection::find
     * @param array $coordinates [lng,lat]
     * @param int $distance
     * @param array $option
     * @return resource $cursor
     */
    public function getWithIn($coordinates = [], $distance = 1000, $option = [])
    {
        $db = $this->di['config']['database']['mongodb']['database'];
        $cursor = $this->di['mongodb']->$db->{$this->collection}->find(
            [
                $this->field => [
                    '$geoWithin' => [
                        '$centerSphere' => [
                            $coordinates,
                            $distance / 6378100
                        ]
                    ]
                ]
            ],
            $option
        );
        return $cursor;
    }

}
