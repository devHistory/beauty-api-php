<?php


namespace App\Http\Controllers\V1;


use App\Http\Models\Accounts;
use Zend\Validator\Uuid;
use Zend\Validator\EmailAddress;
use Zend\Validator\Regex;
use Xxtime\Oauth\OauthAdaptor;
use Carbon\Carbon;
use Exception;

class LoginController extends ControllerBase
{

    private $accountModel;


    public function initialize()
    {
        parent::initialize();
        $this->accountModel = new Accounts();
    }


    /**
     * account
     * password
     */
    public function registerAction()
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
        $this->data['account'] = strtolower($this->data['account']);


        // validator
        $validatorEmail = new EmailAddress();
        $validatorMobile = new Regex(['pattern' => "/^\861[345789]{1}\d{9}$/"]);
        if (!($validatorMobile->isValid($this->data['account']) || $validatorEmail->isValid($this->data['account']))) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'account is invalid'
            ]);
        }


        // if use RPC or Local
        if ($this->di['config']['rpc']['account']) {
            // RPC Account System
            $result = $this->rpc->account('/register', [
                'account'  => $this->data['account'],
                'password' => $this->data['password'],
            ]);
            if ($result === false) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'rpc request error'
                ]);
            }
            if ($result->code != 200) {
                return $this->response->setJsonContent([
                    'code'    => $result->code,
                    'message' => $result->message
                ]);
            }
            if (!($account = $this->accountModel->getAccountByUuid($result->payload->uid))) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'failed'
                ]);
            }
        }
        else {
            // Local Account System
            $account = $this->accountModel->addAccount($this->data['account'], $this->data['password']);
            if (!$account) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'failed, account is already exist'
                ]);

            }
        }


        // output
        $payload = [
            'uid'     => $account['_id'],
            'account' => $account['account'],
            'cTime'   => $account['cTime'],
        ];
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $payload
        ]);
    }


    /**
     * account
     * password
     */
    public function loginAction()
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
        $this->data['account'] = strtolower($this->data['account']);


        // validator
        $validatorEmail = new EmailAddress();
        $validatorMobile = new Regex(['pattern' => "/^\861[345789]{1}\d{9}$/"]);
        if (!($validatorMobile->isValid($this->data['account']) || $validatorEmail->isValid($this->data['account']))) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'account is invalid'
            ]);
        }


        // if use RPC or Local
        if ($this->di['config']['rpc']['account']) {
            // RPC Account System
            $result = $this->rpc->account('/login', [
                'account'  => $this->data['account'],
                'password' => $this->data['password'],
            ]);
            if ($result === false) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'rpc request error'
                ]);
            }
            if ($result->code != 200) {
                return $this->response->setJsonContent([
                    'code'    => $result->code,
                    'message' => $result->message
                ]);
            }
            if (!($account = $this->accountModel->getAccountByUuid($result->payload->uid))) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'failed'
                ]);
            }
        }
        else {
            // Local Account System
            if (!($account = $this->accountModel->getAccount($this->data['account']))) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'no account'
                ]);
            }
            if (empty($account->password) || !password_verify($this->data['password'], $account->password)) {
                return $this->response->setJsonContent([
                    'code'    => 400,
                    'message' => 'error password',
                ]);
            }
        }


        if ($blockMsg = $this->isBlocked($account)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => $blockMsg]);
        }


        // output
        $payload = [
            'uid'     => $account['_id'],
            'account' => $account['account'],
            'cTime'   => $account['cTime'],
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

        $uuid = strtolower($this->data['uuid']);
        if (!$account = $this->accountModel->getAccountByUuid($uuid)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }


        if ($blockMsg = $this->isBlocked($account)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => $blockMsg]);
        }


        // output
        $payload = [
            'uid'     => $account['_id'],
            'account' => $account['account'],
            'cTime'   => $account['cTime'],
        ];
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $payload
        ]);
    }


    /**
     * id
     * token
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
        if (empty($this->data['token'])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid argv token'
            ]);
        }
        if (empty($this->di['config']['oauth'][$type])) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'invalid argv platform'
            ]);
        }


        // verify
        try {
            $oauth = new OauthAdaptor($type, (array)$this->di['config']['oauth'][$type]);
            $response = $oauth->verify($this->data['id'], $this->data['token']);
        } catch (Exception $e) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => $e->getMessage()
            ]);
        }


        // login
        $uuid = $response['id'] . '#' . $type;
        if (!($account = $this->accountModel->getAccountByUuid($uuid))) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }


        if ($blockMsg = $this->isBlocked($account)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => $blockMsg]);
        }


        // output
        $payload = [
            'uid'     => $account['_id'],
            'account' => $account['account'],
            'cTime'   => $account['cTime'],
        ];
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'payload' => $payload
        ]);
    }


    /**
     * check if blocked
     * @param $account
     * @return bool|string
     */
    private function isBlocked(&$account)
    {
        if (!empty($account['blocked']) && $account['blocked'] > time()) {
            return 'blocked ' . Carbon::now()->timestamp($account['blocked'])->diffForHumans();
        }
        return false;
    }

}
