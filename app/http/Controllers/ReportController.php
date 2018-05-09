<?php


namespace App\Http\Controllers;


use App\Http\Models\Posts;
use App\Http\Models\Report;
use Phalcon\Filter;

class ReportController extends ControllerBase
{

    private $postsModel;
    private $reportModel;


    public function initialize()
    {
        parent::initialize();
        $this->postsModel = new Posts();
        $this->reportModel = new Report();
    }

    /**
     * type: user, post
     * id
     */
    public function reportAction()
    {
        $filter = new Filter();
        $type = empty($this->data['type']) ? null : $filter->sanitize($this->data['type'], 'alphanum');
        $id = empty($this->data['id']) ? null : $filter->sanitize($this->data['id'], 'alphanum');
        $content = empty($this->data['content']) ? null : $filter->sanitize($this->data['content'], 'string');

        if (!$type || !$id || !$content) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'missing argv'
            ]);
        }

        // get uid
        $reportUid = '';
        if ($type == 'user') {
            $reportUid = $id;
        }
        elseif ($type == 'post') {
            if (!($posts = $this->postsModel->get($id, ['uid']))) {
                return $this->response->setJsonContent(['code' => 400, 'message' => 'no resource']);
            }
            $reportUid = $posts['uid'];
        }

        if (!$this->reportModel->addReport($this->uid, $type, $id, $reportUid, $content)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }


    /**
     * content
     */
    public function feedbackAction()
    {
        $filter = new Filter();
        $content = empty($this->data['content']) ? null : $filter->sanitize($this->data['content'], 'string');
        if (!$this->reportModel->addFeedback($this->uid, $content)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'failed'
            ]);
        }
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success'
        ]);
    }

}
