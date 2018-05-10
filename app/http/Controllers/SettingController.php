<?php

namespace App\Http\Controllers;


use App\Http\Models\Accounts;
use App\Providers\Components\FilterTrait;

class SettingController extends ControllerBase
{

    use FilterTrait;


    private $accountModel;


    public function initialize()
    {
        parent::initialize();
        $this->accountModel = new Accounts();
    }


    public function nameAction()
    {
        $name = $this->filter($this->data['name'], 'string');
        if (!$name) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'missing argv: name']);
        }
        if (strlen($name) < 3 || strlen($name) > 30) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'length error']);
        }

        if (!$this->accountModel->setName($this->uid, $name)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'name is exist']);
        }
        return $this->response->setJsonContent(['code' => 200, 'message' => 'success']);
    }


    public function attributeAction()
    {
        $data = [
            'birthday' => $this->filter($this->data['birthday'], 'int'),     // 出生19871104
            'gender'   => (int)$this->filter($this->data['gender'], 'int'),  // 性别[1:男 2:女](不可改)
            'locale'   => $this->filter($this->data['locale'], 'string'),    // 所在地
            'hometown' => $this->filter($this->data['hometown'], 'string'),  // 家乡
            'desc'     => $this->filter($this->data['desc'], 'string'),      // 签名
            'avatar'   => $this->filter($this->data['avatar'], 'string'),    // 头像
            'height'   => (int)$this->filter($this->data['height'], 'int'),  // 身高cm
            'weight'   => (int)$this->filter($this->data['weight'], 'int'),  // 身高kg
            'purpose'  => (int)$this->filter($this->data['purpose'], 'int'), // 意向[1:求撩 2:谈恋爱 3:交朋友 4:随缘 5:勿扰]
            'relation' => (int)$this->filter($this->data['relation'], 'int'),// 情感[1:单身 2:恋爱中 3:已婚 4:离异/丧偶]
            'sexual'   => (int)$this->filter($this->data['sexual'], 'int'),  // 取向[1:喜欢男 2:喜欢女 3:双性恋 4:无性恋]
        ];
        $data = array_filter($data);

        // check
        if (!$data) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'missing argv']);
        }
        if (isset($data['height']) && ($data['height'] > 240 || $data['height'] < 135)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'invalid argv: height']);
        }
        if (isset($data['weight']) && ($data['weight'] > 350 || $data['height'] < 20)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'invalid argv: weight']);
        }

        if (!$this->accountModel->setAttribute($this->uid, $data)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'failed']);
        }

        return $this->response->setJsonContent(['code' => 200, 'message' => 'success']);
    }


    // 系统设置
    public function systemAction()
    {
    }

}
