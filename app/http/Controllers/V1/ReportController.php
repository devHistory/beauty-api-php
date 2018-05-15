<?php


namespace App\Http\Controllers\V1;


use App\Http\Models\Posts;
use App\Http\Models\Report;
use App\Providers\Components\FilterTrait;

class ReportController extends ControllerBase
{

    use FilterTrait;


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
        $type = $this->filter($this->data['type'], 'alphanum', null);
        $id = $this->filter($this->data['id'], 'alphanum', null);
        $content = $this->filter($this->data['content'], 'string', null);

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
        $content = $this->filter($this->data['content'], 'string', null);
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
