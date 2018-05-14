<?php

namespace App\Providers\Components;


use Phalcon\Filter;

trait FilterTrait
{

    private $_f;

    public function filter(&$data = null, $rule = '', $default = null)
    {
        if (!$this->_f) {
            $this->_f = new Filter();
        }
        return empty($data) ? $default : $this->_f->sanitize($data, $rule);
    }

}
