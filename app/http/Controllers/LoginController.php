<?php


namespace App\Http\Controllers;


use App\Http\Models\AccountsModel;
use Zend\Validator\Uuid;
use Zend\Validator\EmailAddress;
use Zend\Validator\Regex;

class LoginController extends ControllerBase
{

    private $accountModel;


    public function initialize()
    {
        parent::initialize();
        $this->accountModel = new AccountsModel();
    }


    /**
     * account
     * password
     */
    public function accountAction()
    {
        if (empty($this->data['account'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing account'
            ]);
        }
        if (empty($this->data['password'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing password'
            ]);
        }

        // validator
        $validatorEmail = new EmailAddress();
        $validatorMobile = new Regex(['pattern' => "/^\861[345789]{1}\d{9}$/"]);
        if (!($validatorMobile->isValid($this->data['account']) || $validatorEmail->isValid($this->data['account']))) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'account is invalid'
            ]);
        }

        // RPC
        $result = $this->rpc->account('/login', [
            'account'  => $this->data['account'],
            'password' => $this->data['password'],
        ]);

        if ($result->code != 200) {
            return $this->response->setJsonContent([
                'code'    => $result->code,
                'message' => $result->message
            ]);
        }

        // output
        $payload = [
            'uid'        => $result->payload->uid,
            'account'    => $result->payload->account,
            'createTime' => $result->payload->createTime,
        ];
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $payload
        ]);
    }


    /**
     * uuid
     */
    public function deviceAction()
    {
        if (empty($this->data['uuid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing uuid'
            ]);
        }
        $validator = new Uuid();
        if (!$validator->isValid($this->data['uuid'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid uuid'
            ]);
        }

        if (!($account = $this->accountModel->getAccountByUuid($this->data['uuid']))) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }

        // output
        $payload = [
            'uid'        => $account['_id'],
            'account'    => $account['account'],
            'createTime' => $account['createTime'],
        ];
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $payload
        ]);
    }


    /**
     * id
     */
    public function platformAction()
    {
        $type = $this->dispatcher->getParam('type');
        if (empty($this->data['id'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid argv id'
            ]);
        }
        $uuid = $this->data['id'] . '#' . $type;


        // TODO :: check


        // login
        if (!($account = $this->accountModel->getAccountByUuid($uuid))) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }

        // output
        $payload = [
            'uid'        => $account['_id'],
            'account'    => $account['account'],
            'createTime' => $account['createTime'],
        ];
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $payload
        ]);
    }

}
