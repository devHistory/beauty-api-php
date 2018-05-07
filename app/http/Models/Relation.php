<?php

namespace App\Http\Models;


use Phalcon\Mvc\Model;

class Relation extends Model
{

    // 添加好友
    public function addFriends($friendId = '', $uid = '')
    {
        $key = 'friends|' . $uid;
        $this->di['redis']->sAdd($key, $friendId);

        // delete from cache
        $this->di['cache']->del('_' . $key);

        return true;
    }


    // 删除好友
    public function delFriends($friendId = '', $uid = '')
    {
        $key = 'friends|' . $uid;
        $this->di['redis']->sRem($key, $friendId);

        // delete from cache
        $this->di['cache']->del('_' . $key);

        return true;
    }


    // 好友列表
    public function getFriends($uid = '')
    {
        $key = 'friends|' . $uid;

        // get from cache
        $data = $this->di['cache']->get('_' . $key);
        if ($data) {
            return json_decode($data, true);
        }

        $members = $this->di['redis']->sMembers($key);
        $data = $this->di['utils']->fillUserByCache($members, ['name', 'level', 'desc']);
        $this->di['cache']->set('_' . $key, json_encode($data), 86400 * 1);
        return $data;
    }


    // 关注用户
    public function addFollow($followId = '', $uid = '')
    {
        $key1 = 'followers|' . $followId;
        $this->di['redis']->sAdd($key1, $uid);

        $key2 = 'following|' . $uid;
        $this->di['redis']->sAdd($key2, $followId);

        // delete from cache
        $this->di['cache']->del('_' . $key1);
        $this->di['cache']->del('_' . $key2);

        return true;
    }


    // 取消关注
    public function delFollow($followId = '', $uid = '')
    {
        $key1 = 'followers|' . $followId;
        $this->di['redis']->sRem($key1, $uid);

        $key2 = 'following|' . $uid;
        $this->di['redis']->sRem($key2, $followId);

        // delete from cache
        $this->di['cache']->del('_' . $key1);
        $this->di['cache']->del('_' . $key2);

        return true;
    }


    // 粉丝列表
    public function listFollowers($uid = '')
    {
        $key = 'followers|' . $uid;

        // get from cache
        $data = $this->di['cache']->get('_' . $key);
        if ($data) {
            return json_decode($data, true);
        }

        $members = $this->di['redis']->sMembers($key);
        $data = $this->di['utils']->fillUserByCache($members, ['name', 'level', 'desc']);
        $this->di['cache']->set('_' . $key, json_encode($data), 86400 * 1);
        return $data;
    }


    // 关注列表
    public function listFollowing($uid = '')
    {
        $key = 'following|' . $uid;

        // get from cache
        $data = $this->di['cache']->get('_' . $key);
        if ($data) {
            return json_decode($data, true);
        }

        $members = $this->di['redis']->sMembers($key);
        $data = $this->di['utils']->fillUserByCache($members, ['name', 'level', 'desc']);
        $this->di['cache']->set('_' . $key, json_encode($data), 86400 * 1);
        return $data;
    }

}
