<?php

namespace App\Providers\Components;


use Phalcon\DI;

trait AesTrait
{

    /**
     * TODO :: $options = OPENSSL_RAW_DATA OPENSSL_ZERO_PADDING
     * @see http://php.net/manual/en/function.openssl-encrypt.php
     *
     * @param string $secret
     * @param string $plaintext
     * @return array
     */
    public function encrypt($secret = '', $plaintext = '')
    {
        $cipher = DI::getDefault()['config']['env']['cipher'];
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($plaintext, $cipher, $secret, OPENSSL_RAW_DATA, $iv);
        return [$iv, $encrypted];

    }

    /**
     * @param string $secret
     * @param string $iv
     * @param string $encrypted
     * @return string
     */
    public function decrypt($secret = '', $iv = '', $encrypted = '')
    {
        $cipher = DI::getDefault()['config']['env']['cipher'];
        return openssl_decrypt($encrypted, $cipher, $secret, OPENSSL_RAW_DATA, $iv);
    }

}
