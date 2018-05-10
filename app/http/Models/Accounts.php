<?php


namespace App\Http\Models;


use Phalcon\Mvc\Model;
use MongoDB\BSON\ObjectId;
use Exception;

class Accounts extends Model
{

    public function exists($id)
    {
        $k = '_account|' . $id;
        if ($this->di['cache']->get($k)) {
            return true;
        }
        if ($this->getAccountById($id)) {
            return true;
        }
        return false;
    }


    public function addAccount($account = '', $password = '', $options = [])
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
                '_id'     => $id->__toString(),
                'account' => $account,
                'cTime'   => time(),
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
        $account = $this->addAccount($uuid, null);
        if (!$account) {
            return false;
        }

        return $account;
    }


    public function setAttribute($uid = '', $data = [])
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']['database']['mongodb']['database'];
        $mongodb->$db->accounts->updateOne(
            ['_id' => $uid],
            ['$set' => $data + ['mTime' => time()]]
        );
        return true;
    }


    public function setName($uid = '', $name = '')
    {
        if (!$name) {
            return false;
        }
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']['database']['mongodb']['database'];

        // find account
        if (!$account = $mongodb->$db->accounts->findOne(['_id' => $uid])) {
            return false;
        }
        if (isset($account['name']) && $name == $account['name']) {
            return true;
        }

        try { // TODO :: 事物
            $mongodb->$db->nicknames->insertOne(['_id' => md5($name), 'uid' => $uid]);
            $this->setAttribute($uid, ['name' => $name]);
            if (!empty($account['name'])) {
                $mongodb->$db->nicknames->deleteOne(['_id' => md5($account['name'])]);
            }
            $this->di['cache']->del('_account|' . $uid);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}
