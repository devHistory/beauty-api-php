<?php

namespace App\Http\Models;


use Phalcon\Mvc\Model;

class Favorites extends Model
{

    public function add($uid = '', $id = '', $type = 'post')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;

        $data = null;
        if ($type == 'post') {
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


    public function del($uid = '', $id = '', $type = 'post')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;

        return $mongodb->$db->favorites->updateOne(
            ['_id' => $uid],
            [
                '$pull' => [$type => ['id' => $id]],
                '$set'  => ['mTime' => time()]
            ]
        );
    }


    public function get($uid = '', $type = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;

        $data = $mongodb->$db->favorites->findOne(
            ['_id' => $uid],
            [
                'projection' => ['_id' => 0, $type => 1],
            ]
        );
        return $data;
    }

}
