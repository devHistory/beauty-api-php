<?php

namespace App\Http\Controllers\V1;


use App\Http\Models\Accounts;
use App\Http\Models\Relation;

class FriendsController extends ControllerBase
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
    public function storeAction()
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
        if (!$this->accountModel->exists($this->data['uid'])) {
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
    public function destroyAction($uid)
    {
        if (empty($uid)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv uid'
            ]);
        }

        $this->relationModel->delFriends($uid, $this->uid);

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }


    // 好友列表
    public function indexAction()
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
}
