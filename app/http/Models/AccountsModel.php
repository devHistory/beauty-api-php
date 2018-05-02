<?php


namespace App\Http\Models;


use Phalcon\Mvc\Model;
use MongoDB\BSON\ObjectId;
use Exception;

class AccountsModel extends Model
{

    public function createAccount($account = '', $password = '', $options = [])
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']['database']['mongodb']['database'];
        $id = new ObjectId();
        if ($this->getAccount($account)) {
            return false;
        }

        // TODO :: 事物
        try {
            $mongodb->$db->ids->insertOne([
                '_id' => $account,
                'uid' => $id->__toString(),
            ]);
            $basic = [
                '_id'        => $id->__toString(),
                'account'    => $account,
                'createTime' => time(),
            ];
            empty($password) ? null : $basic['password'] = password_hash($password, PASSWORD_DEFAULT);
            $mongodb->$db->accounts->insertOne($basic + $options);
        } catch (Exception $e) {
            return false;
        }

        unset($basic['password']);
        return $basic;
    }


    public function getAccountById($id = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']['database']['mongodb']['database'];
        if (!($result = $mongodb->$db->accounts->findOne(['_id' => $id]))) {
            return false;
        }
        return $result;
    }


    public function getAccount($account = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']['database']['mongodb']['database'];
        if (!($result = $mongodb->$db->ids->findOne(['_id' => $account]))) {
            return false;
        }
        return $this->getAccountById($result->uid);
    }


    public function getAccountByUuid($uuid = '')
    {
        // get
        $account = $this->getAccount($uuid);
        if ($account) {
            return $account;
        }

        // create
        $account = $this->createAccount($uuid, null);
        if (!$account) {
            return false;
        }

        return $account;
    }

}
