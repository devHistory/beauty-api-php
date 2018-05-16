<?php

namespace App\Providers\Components;


use Firebase\JWT\JWT;
use Phalcon\Mvc\User\Component;

class Support extends Component
{

    /**
     * @link https://github.com/firebase/php-jwt/
     * @link https://tools.ietf.org/html/draft-ietf-oauth-json-web-token-32
     * @param array|string $data
     * @param int $timeout
     * @return string
     */
    public function createToken($data = '', $timeout = 3600)
    {
        $timestamp = time();
        $key = $this->di['config']['env']['token'];
        $token = array(
            // "sub"  => $sub,                  // 主题
            // "aud"  => "",                    // 接收方
            "iss" => $_SERVER['SERVER_NAME'],   // 签发者
            "iat" => $timestamp,                // 签发时间
            "nbf" => $timestamp,                // Not Before
            "exp" => $timestamp + $timeout,     // 过期
            "dat" => $data                      // 数据
        );
        $token = JWT::encode($token, $key);
        return $token;
    }


    public function verifyToken($token = '')
    {
        $key = $this->di['config']['env']['token'];
        try {
            JWT::$leeway = 300; // 允许误差秒数
            return JWT::decode($token, $key, array('HS256'));
        } catch (Exception $e) {
            return false;
        }
    }

}
