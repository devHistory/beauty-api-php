<?php

namespace App\Http\Controllers\V1;


use App\Http\Models\Favorites;
use App\Providers\Components\FilterTrait;

class FavoritesController extends ControllerBase
{

    use FilterTrait;


    private $favoritesModel;


    private $allowType = ['post'];


    public function beforeExecuteRoute()
    {
        parent::beforeExecuteRoute();
        $this->favoritesModel = new Favorites();
    }


    // 列表 type
    public function getAction()
    {
        $type = $this->filter($this->data['type'], 'alphanum', 'post');

        if (!in_array($type, $this->allowType)) {
            return $this->response->setJsonContent(['code' => 400, 'message' => 'error argv']);
        }

        $data = $this->favoritesModel->get($this->uid, $type);
        if (!$data) {
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
    public function addAction()
    {
        $id = $this->filter($this->data['id'], 'alphanum', null);
        $type = $this->filter($this->data['type'], 'alphanum', 'post');

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


    // 删除 id, type
    public function delAction()
    {
        $id = $this->dispatcher->getParam('id');
        $type = $this->filter($this->data['type'], 'alphanum', 'post');

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
