<?php


namespace App\Providers\Exception;


use Exception;

class RequestException extends Exception
{


    public function __toString()
    {
        exit(json_encode([
            'code'    => $this->getCode(),
            'message' => $this->getMessage()
        ]));
    }

}
