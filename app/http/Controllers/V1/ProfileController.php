<?php

namespace App\Http\Controllers\V1;


use App\Http\Models\Accounts;
use App\Providers\Components\UtilsTrait;
use Zend\Validator\Regex;

class ProfileController extends ControllerBase
{

    use UtilsTrait;


    private $accountModel;


    public function initialize()
    {
        parent::initialize();
        $this->accountModel = new Accounts();
    }


    public function showAction()
    {
        $data = $this->accountModel->getAccountById($this->uid);
        $data['uid'] = $data['_id'];
        unset($data['_id'], $data['password']);
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $data
        ]);
    }


    public function updateAction()
    {
        $do = $this->request->get('do');
        switch ($do) {
            case 'name':
                return $this->setName();
                break;
            case 'password':
                return $this->setPassword();
                break;
            case 'attribute':
                return $this->setAttribute();
            default :
                throw new \Exception('invalid method');
        }
    }


    /**
     * name
     */
    private function setName()
    {
        $name = $this->filter($this->data['name'], 'string');
        if (!$name) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'missing argv: name']);
        }
        if (strlen($name) < 3 || strlen($name) > 30) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'name: invalid length']);
        }

        if (!$this->accountModel->setName($this->uid, $name)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'name is exist']);
        }
        return $this->response->setJsonContent(['code' => 200, 'message' => 'success']);
    }


    /**
     * old
     * pass
     */
    private function setPassword()
    {
        $oldPass = $this->filter($this->data['old'], 'string');
        $newPass = $this->filter($this->data['pass'], 'string');

        // 复杂度
        if ((strlen($newPass) < 6) || !preg_match("/[0-9]+/", $newPass) || !preg_match("/[a-zA-Z]+/", $newPass)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'too sample']);
        }

        // if use RPC or Local
        if ($this->di['config']['rpc']['account']) {
            $account = $this->accountModel->getAccountById($this->uid);
            $validator = new Regex(['pattern' => "/^[a-f0-9]{24}$/"]);
            if (!$validator->isValid($account['account'])) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'not bind account'
                ]);
            }
            $response = $this->rpc->account('/setting/password', [
                'uid'  => $account['account'],
                'old'  => $oldPass,
                'pass' => $newPass,
            ]);
            if ($response->code != 200) {
                return $this->response->setJsonContent([
                    'code'    => $response->code,
                    'message' => $response->message
                ]);
            }
            return $this->response->setJsonContent([
                'code'    => 200,
                'message' => 'success'
            ]);
        }
        else {
            if (!$this->accountModel->setPass($this->uid, $oldPass, $newPass)) {
                return $this->response->setJsonContent(['code' => 400, 'message' => 'old password error']);
            }
        }
        return $this->response->setJsonContent(['code' => 200, 'message' => 'success']);
    }


    /**
     * set attribute
     */
    private function setAttribute()
    {
        $data = [
            'birthday' => $this->filter($this->data['birthday'], 'int'),      // 出生19871104
            'gender'   => (int)$this->filter($this->data['gender'], 'int!'),  // 性别[1:男 2:女](不可改)
            'locale'   => $this->filter($this->data['locale'], 'string'),     // 所在地
            'hometown' => $this->filter($this->data['hometown'], 'string'),   // 家乡
            'desc'     => $this->filter($this->data['desc'], 'string'),       // 签名
            'avatar'   => $this->filter($this->data['avatar'], 'string'),     // 头像
            'height'   => (int)$this->filter($this->data['height'], 'int!'),  // 身高cm
            'weight'   => (int)$this->filter($this->data['weight'], 'int!'),  // 体重kg
            'purpose'  => (int)$this->filter($this->data['purpose'], 'int!'), // 意向[1:求撩 2:谈恋爱 3:交朋友 4:随缘 5:勿扰]
            'relation' => (int)$this->filter($this->data['relation'], 'int!'),// 情感[1:单身 2:恋爱中 3:已婚 4:离异/丧偶]
            'sexual'   => (int)$this->filter($this->data['sexual'], 'int!'),  // 取向[1:喜欢男 2:喜欢女 3:双性恋 4:无性恋]
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

}
