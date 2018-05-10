<?php

namespace App\Http\Models;


use Phalcon\Mvc\Model;
use MongoDB\BSON\ObjectId;
use Exception;

class Posts extends Model
{

    // 检查
    public function exists($id = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;
        if ($mongodb->$db->posts->findOne(['_id' => $id], ['projection' => ['_id' => 1]])) {
            return true;
        }
        return false;
    }


    // 获取
    public function get($postId = '', $attr = [])
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;
        if ($attr) {
            $projection = [];
            if (is_array($attr)) {
                foreach ($attr as $v) {
                    $projection[$v] = 1;
                }
            }
            return $mongodb->$db->posts->findOne(['_id' => $postId], ['projection' => $projection]);
        }
        return $mongodb->$db->posts->findOne(['_id' => $postId]);
    }


    // 添加
    public function add($uid = '', $content = '', $attach = [])
    {
        if (!$uid) {
            return false;
        }

        // insert into database
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;
        $id = new ObjectId();
        try {
            $postData = [
                '_id'     => $id->__toString(),
                'uid'     => $uid,
                'content' => $content,
            ];
            $postData = $postData + $attach;
            $mongodb->$db->posts->insertOne($postData);

            // TODO :: 非匿名内容则推送
            if (empty($postData['anonymous'])) {
                // TODO :: push to TimeLine Feed
            }
        } catch (Exception $e) {
            return false;
        }

        return $id->__toString();
    }


    // 删除 TODO :: trash软删除
    public function del($uid = '', $postId = '')
    {
        if (!$post = $this->get($postId)) {
            return false;
        }

        // 检查权限
        if ($post->uid != $uid) {
            return false;
        }

        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;

        // 删评论 :: 主题删除后相关评论被删除
        if (isset($post->comment)) {
            foreach ($post->comment as $comment) {
                $mongodb->$db->comments->deleteOne(['_id' => $comment->cid]);
            }
        }

        // 删主题
        $mongodb->$db->posts->deleteOne(['_id' => $postId]);

        // TODO :: push to TimeLine Feed

        return true;
    }


    // 添加评论
    public function addComment($uid = '', $postId = '', $content = '')
    {
        if (!$uid || !$postId || !$content) {
            return false;
        }
        if (!$this->exists($postId)) {
            return false;
        }

        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;
        $oid = new ObjectId();
        $timestamp = time();

        $mongodb->$db->posts->updateOne(
            ['_id' => $postId],
            [
                '$inc'  => ['commentNum' => 1],
                '$push' => [
                    'comment' => [
                        'cid'     => $oid->__toString(),
                        'uid'     => $uid,
                        'content' => $content,
                        'cTime'   => $timestamp,
                    ]
                ],
            ]
        );

        $mongodb->$db->comments->insertOne([
            '_id'     => $oid->__toString(),
            'pid'     => $postId,
            'uid'     => $uid,
            'content' => $content,
            'cTime'   => $timestamp,
        ]);
        return $oid->__toString();
    }


    // 删除评论
    public function delComment($uid = '', $commentId = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->database->mongodb->database;

        // find comment
        $comment = $mongodb->$db->comments->findOne(
            ['_id' => $commentId],
            ['projection' => ['pid' => 1, 'uid' => 1]]
        );

        // check
        if (!$comment) {
            return false;
        }
        if ($comment->uid != $uid) {
            return false;
        }

        // update post
        $mongodb->$db->posts->updateOne(
            ['_id' => $comment->pid],
            [
                '$inc'  => ['commentNum' => -1],
                '$pull' => ['comment' => ['cid' => $commentId]]
            ]
        );

        // delete comment
        $mongodb->$db->comments->deleteOne(['_id' => $commentId]);

        return true;
    }

}
