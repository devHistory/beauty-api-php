<?php

namespace App\Http\Controllers;


use App\Http\Models\Accounts;
use App\Http\Models\Relation;

class RelationController extends ControllerBase
{

    private $relationModel;


    private $accountModel;


    public function initialize()
    {
        parent::initialize();
        $this->relationModel = new Relation();
        $this->accountModel = new Accounts();
    }


    // 添加好友
    public function addFriendsAction()
    {
        if (empty($this->data['uid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv uid'
            ]);
        }
        if ($this->data['uid'] == $this->uid) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid argv uid'
            ]);
        }
        if (!$this->accountModel->existsAccount($this->data['uid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'uid is not exists'
            ]);
        }

        $this->relationModel->addFriends($this->data['uid'], $this->uid);

        // TODO :: 推送消息给对方

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }


    // 删除好友
    public function delFriendsAction()
    {
        if (empty($this->data['uid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv uid'
            ]);
        }

        $this->relationModel->delFriends($this->data['uid'], $this->uid);

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }


    // 好友列表
    public function getFriendsAction()
    {
        $data = $this->relationModel->getFriends($this->uid);
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => [
                'num'  => count($data),
                'list' => $data
            ]
        ]);
    }


    // 关注
    public function addFollowAction()
    {
        if (empty($this->data['uid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv uid'
            ]);
        }
        if ($this->data['uid'] == $this->uid) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid argv uid'
            ]);
        }
        if (!$this->accountModel->existsAccount($this->data['uid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'uid is not exists'
            ]);
        }

        $this->relationModel->addFollow($this->data['uid'], $this->uid);

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }


    // 取消关注
    public function delFollowAction()
    {
        if (empty($this->data['uid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv uid'
            ]);
        }
        if ($this->data['uid'] == $this->uid) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid argv uid'
            ]);
        }

        $this->relationModel->delFollow($this->data['uid'], $this->uid);

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }


    // 粉丝列表
    public function followersAction()
    {
        $data = $this->relationModel->listFollowers($this->uid);
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => [
                'num'  => count($data),
                'list' => $data
            ]
        ]);
    }


    // 关注列表
    public function followingAction()
    {
        $data = $this->relationModel->listFollowing($this->uid);
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => [
                'num'  => count($data),
                'list' => $data
            ]
        ]);
    }
}
