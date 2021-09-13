<?php

namespace App\Http\Controllers;

class HashPasswordController extends Controller
{
    private $ciphering = "AES-128-CTR";
    private $options = 0;
    private $encryption_iv = '1205202109480000';
    private $encryption_key = "depo-cps";

    public function hashPassword(string $password) {
        return openssl_encrypt($password, $this->ciphering, $this->encryption_key, $this->options, $this->encryption_iv);
    }

    public function unHashPassword(string $encrypted) {
        return openssl_decrypt($encrypted, $this->ciphering, $this->encryption_key, $this->options, $this->encryption_iv);
    }
}
