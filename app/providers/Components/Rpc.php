<?php

namespace App\Providers\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Phalcon\DI;

class Rpc
{

    public function account($uri = '', $data = '')
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }

        // request
        $client = new Client([
            'base_uri' => DI::getDefault()->get('config')['rpc']['account'],
            'headers'  => ['User-Agent' => 'AccountApp/1.0']
        ]);
        $request = new Request('POST', $uri, [], $data);
        $response = $client->send($request, ['timeout' => 3]);

        // response
        if ($response->getBody()->getSize() == 0) {
            return false;
        }
        return json_decode($response->getBody()->getContents());
    }

}
