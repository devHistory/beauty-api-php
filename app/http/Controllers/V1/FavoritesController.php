<?php

namespace App\Http\Controllers\V1;


use App\Http\Models\Favorites;
use App\Providers\Components\UtilsTrait;

class FavoritesController extends ControllerBase
{

    use UtilsTrait;


    private $favoritesModel;


    private $allowType = ['posts'];


    public function initialize()
    {
        parent::initialize();
        $this->favoritesModel = new Favorites();
    }


    // 列表 type
    public function indexAction()
    {
        $type = $this->request->get('type');
        if (!in_array($type, $this->allowType)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'error argv']);
        }

        $data = $this->favoritesModel->getList($this->uid, $type);
        if (!count($data)) {
            return $this->response->setJsonContent([
                'code'    => 400,
                'message' => 'no data'
            ]);
        }
        $data = $this->utils->fillUserByKey($data[$type], 'uid', ['name']);

        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
            'data'    => [
                'num'  => count($data),
                'list' => $data
            ]
        ]);
    }


    // 收藏 id, type
    public function storeAction()
    {
        $id = $this->filter($this->data['id'], 'alphanum', null);
        $type = $this->filter($this->data['type'], 'alphanum', 'posts');


        // check
        if (!$id || !$type || !in_array($type, $this->allowType)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'error argv']);
        }

        // add
        if (!$this->favoritesModel->add($this->uid, $id, $type)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'failed']);
        }

        // return
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
        ]);
    }


    /**
     * 删除 id, type
     * TODO :: type参数位置 uri or body
     * @param $id
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function destroyAction($id)
    {
        $type = $this->filter($this->data['type'], 'alphanum', 'posts');

        // check
        if (!$id || !$type || !in_array($type, $this->allowType)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'error argv']);
        }

        // delete
        if (!$this->favoritesModel->del($this->uid, $id, $type)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'failed']);
        }

        // return
        return $this->response->setJsonContent([
            'code'    => 200,
            'message' => 'success',
        ]);
    }

}
