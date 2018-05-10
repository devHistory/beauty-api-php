<?php

namespace App\Http\Controllers;


use App\Http\Models\Favorites;
use Phalcon\Filter;

class FavoritesController extends ControllerBase
{

    private $favoritesModel;


    private $allowType = ['post'];


    public function initialize()
    {
        parent::initialize();
        $this->favoritesModel = new Favorites();
    }


    // 列表 type
    public function getAction()
    {
        $filter = new Filter();
        $type = empty($this->data['type']) ? 'post' : $filter->sanitize($this->data['type'], 'alphanum');

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
        $filter = new Filter();
        $id = empty($this->data['id']) ? null : $filter->sanitize($this->data['id'], 'alphanum');
        $type = empty($this->data['type']) ? 'post' : $filter->sanitize($this->data['type'], 'alphanum');

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
        $filter = new Filter();
        $id = $this->dispatcher->getParam('id');
        $type = empty($this->data['type']) ? 'post' : $filter->sanitize($this->data['type'], 'alphanum');

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
