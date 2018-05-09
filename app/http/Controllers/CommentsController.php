<?php

namespace App\Http\Controllers;


use App\Http\Models\Posts;
use Phalcon\Filter;

class CommentsController extends ControllerBase
{

    private $postModel;


    public function initialize()
    {
        parent::initialize();
        $this->postModel = new Posts();
    }


    /**
     * 添加评论
     * pid, content
     */
    public function addAction()
    {
        $filter = new Filter();
        $postId = empty($this->data['pid']) ? '' : $filter->sanitize($this->data['pid'], 'alphanum');
        $content = empty($this->data['content']) ? '' : $filter->sanitize($this->data['content'], 'string');

        if (!$postId || !$content) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv: pid or content'
            ]);
        }

        if (!$this->postModel->addComment($this->uid, $postId, $content)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed',
            ]);
        }

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
        ]);
    }


    /**
     * 删除评论
     */
    public function delAction()
    {
        $commentId = $this->dispatcher->getParam('commentId');
        if (!$commentId) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv: commentId'
            ]);
        }

        if (!$this->postModel->delComment($this->uid, $commentId)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
        ]);
    }

}
