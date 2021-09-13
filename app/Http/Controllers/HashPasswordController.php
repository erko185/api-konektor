<?php

namespace App\Http\Controllers;

class HashPasswordController extends Controller
{
private $encrypt_method = "AES-256-CBC";
private $secret_key = "AA74CDCC2BBRT935136HH7B63C27";
private $secret_iv = "RwS3cr3t";
private $key = "";

public function __construct()
{
    $this->key = hash("sha256", $this->secret_key);
}

    function rw_hash($string, $encrypt = true)
    {
        $iv = substr(hash("sha256", $this->secret_iv), 0, 16); // sha256 is hash_hmac_algo
        if ($encrypt) {
            $output = openssl_encrypt($string, $this->encrypt_method, $this->key, 0, $iv);
            $output = base64_encode($output);
        } else {
            $output = openssl_decrypt(base64_decode($string), $this->encrypt_method, $this->key, 0, $iv);
        }
        return $output;
    }
}
