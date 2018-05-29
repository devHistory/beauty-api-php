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
     * 附近数据 并计算距离 [距离单位: near GeoJSON格式单位为米，坐标对时单位为弧度]
     * @link https://docs.mongodb.com/php-library/v1.3/tutorial/commands/index.html
     * @link https://docs.mongodb.com/manual/reference/command/geoNear/#dbcmd.geoNear
     * @param array $coordinates [lng,lat]
     * @param int $distance
     * @param null $filter
     * @param array $option [limit]
     * @return resource $cursor
     */
    public function getGeoNear($coordinates = [], $distance = 1000, $filter = null, $option = [])
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
            'maxDistance' => $distance,
        ];
        if (is_array($filter)) {
            $query['query'] = $filter;
        }
        $query['num'] = empty($option['limit']) ? 100 : $option['limit'];
        $cursor = $this->di['mongodb']->$db->command($query);
        return $cursor;
    }


    /**
     * 附近数据 不计算距离 [性能好于geoNear]
     * 地球半径: 3,963.2英尺 或6,378.1千米.
     * @link https://docs.mongodb.com/manual/reference/operator/query/nearSphere/#op._S_nearSphere
     * @link https://docs.mongodb.com/manual/geospatial-queries/
     * @link https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-find/#phpmethod.MongoDB%5CCollection::find
     * @param array $coordinates [lng,lat]
     * @param int $distance
     * @param null $filter
     * @param array $option
     * @return resource $cursor
     */
    public function getNear($coordinates = [], $distance = 1000, $filter = null, $option = [])
    {
        $db = $this->di['config']['database']['mongodb']['database'];
        $query = [
            $this->field => [
                '$nearSphere' => [
                    '$geometry'    => [
                        'type'        => 'Point',
                        'coordinates' => $coordinates,
                    ],
                    '$minDistance' => 0,
                    '$maxDistance' => $distance,
                ]
            ]
        ];
        if (is_array($filter)) {
            $query += $filter;
        }
        if (empty($option['limit'])) {
            $option['limit'] = 100;
        }
        $cursor = $this->di['mongodb']->$db->{$this->collection}->find($query, $option);
        return $cursor;
    }

}
