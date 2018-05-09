<?php

namespace App\Providers\Components;


use Phalcon\Mvc\User\Component;

class Utils extends Component
{

    /**
     * 填充用户信息 - 从缓存获取
     * @param array $uid
     * @param array $fields
     * @return array
     */
    public function fillUserByCache($uid = [], $fields = ['name'])
    {
        if (!$uid) {
            return [];
        }

        $oneLine = false;
        if (is_string($uid)) {
            $oneLine = true;
            $uid = [$uid];
        }

        // Get From Cache
        $cacheKeys = [];
        foreach ($uid as $u) {
            $cacheKeys[] = '_account|' . $u;
        }
        $cacheData = $this->cache->mget($cacheKeys);
        $dataDict = [];
        foreach ($cacheData as $k => $v) {
            if (!$v) {
                $miss[] = $uid[$k];
                continue;
            }
            $dataDict[$uid[$k]] = json_decode($v, true);
        }

        // 查询 MongoDB
        if (!empty($miss)) {
            // 缓存键名
            $projection = [
                'name'    => 1,
                'gender'  => 1,
                'age'     => 1,
                'certify' => 1,
                'level'   => 1,
                'avatar'  => 1,
                'desc'    => 1,
                'uuid'    => 1,
            ];

            $uidList = [];
            foreach ($miss as $u) {
                $uidList[] = $u;
            }

            $db = $this->config['database']['mongodb']['database'];
            $accounts = $this->mongodb->$db->accounts->find(
                ['_id' => ['$in' => $uidList]],
                ['projection' => $projection]
            );
            foreach ($accounts as $account) {
                $oid = $account->_id;
                $this->cache->set('_account|' . $oid, json_encode($account), 86400 * 14);
                $dataDict[$oid] = $account;
            }
        }


        $result = [];
        foreach ($uid as $u) {
            $d = [];
            foreach ($fields as $f) {
                if (empty($dataDict[$u][$f])) {
                    $d[$f] = '';
                }
                else {
                    $d[$f] = $dataDict[$u][$f];
                }
            }
            $result[] = ['uid' => $u] + $d;
        }
        if ($oneLine == true) {
            return array_pop($result);
        }
        return $result;
    }


    /**
     * 填充用户信息
     * @param null $data
     * @param null $key
     * @param array $field
     * @return array
     */
    public function fillUserByKey($data = null, $key = null, $field = ['name'])
    {
        foreach ($data as $value) {
            $uid[] = $value[$key];
        }
        if (empty($uid)) {
            return [];
        }
        $accounts = $this->fillUserByCache($uid, $field);

        $dict = [];
        foreach ($accounts as $u) {
            $uid = $u['uid'];
            $dict[$uid] = $u;
        };

        foreach ($data as $k => $v) {
            if (!isset($dict[$v[$key]])) {
                continue;
            }
            foreach ($field as $f) {
                if (empty($dict[$v[$key]][$f])) {
                    $data[$k][$f] = '';
                }
                else {
                    $data[$k][$f] = $dict[$v[$key]][$f];
                }
            }
        }
        return $data;
    }

}
