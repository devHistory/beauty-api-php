<?php

namespace App\Providers\Components;


use Phalcon\Filter;

trait FilterTrait
{

    private $f;

    public function filter(&$data = null, $rule = '', $default = null)
    {
        if (!$this->f) {
            $this->f = new Filter();
        }
        return empty($data) ? $default : $this->f->sanitize($data, $rule);
    }

}
