<?php

namespace App\Http\Models;


use Phalcon\Mvc\Model;

class Favorites extends Model
{

    public function add($uid = '', $id = '', $type = 'posts')
    {
        $mongodb = $this->di['mongodb'];
        $db = config('database.mongodb.db');

        $data = null;
        if ($type == 'posts') {
            $data = $mongodb->$db->posts->findOne(
                ['_id' => $id],
                [
                    'projection' => [
                        '_id'     => 0,
                        'uid'     => 1,
                        'content' => 1,
                        'picture' => 1,
                        'voice'   => 1,
                        'video'   => 1,
                    ]
                ]
            );
            if (!$data) {
                return false;
            }
        }

        $insertData = ['id' => $id] + (array)$data;

        return $mongodb->$db->favorites->updateOne(
            ['_id' => $uid],
            [
                '$addToSet' => [$type => $insertData],
                '$set'      => ['mTime' => time()]
            ],
            ['upsert' => true]
        );
    }


    public function del($uid = '', $id = '', $type = 'posts')
    {
        $mongodb = $this->di['mongodb'];
        $db = config('database.mongodb.db');

        return $mongodb->$db->favorites->updateOne(
            ['_id' => $uid],
            [
                '$pull' => [$type => ['id' => $id]],
                '$set'  => ['mTime' => time()]
            ]
        );
    }


    public function getList($uid = '', $type = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = config('database.mongodb.db');

        $data = $mongodb->$db->favorites->findOne(
            ['_id' => $uid],
            [
                'projection' => ['_id' => 0, $type => 1],
            ]
        );
        return $data;
    }

}
