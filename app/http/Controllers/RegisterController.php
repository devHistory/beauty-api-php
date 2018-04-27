<?php


namespace App\Http\Controllers;


use App\Http\Models\AccountsModel;
use Zend\Validator\EmailAddress;
use Zend\Validator\Regex;

class RegisterController extends ControllerBase
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
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function accountAction()
    {
        if (empty($this->data['account'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv account'
            ]);
        }
        if (empty($this->data['password'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv password'
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

        // create
        $account = $this->accountModel->createAccount($this->data['account'], $this->data['password']);
        if (!$account) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed, account is already exist'
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
