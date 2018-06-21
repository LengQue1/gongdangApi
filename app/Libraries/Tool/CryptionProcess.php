<?php
namespace App\Libraries\Tool;

class CryptionProcess {

    // des加密
    static function encrypt($openid, $key, $cipher = MCRYPT_DES,$modes = MCRYPT_MODE_ECB)
    {
        $passcrypt = mcrypt_encrypt(MCRYPT_DES, $key, trim($openid), MCRYPT_MODE_ECB);
        $encode = base64_encode($passcrypt);
        return $encode;
    }

    // des解密
    static function decrypt($decrypt, $key, $cipher = MCRYPT_DES,$modes = MCRYPT_MODE_ECB)
    {
        $decoded = base64_decode(trim($decrypt));
        $decrypted = trim(mcrypt_decrypt(MCRYPT_DES, $key, $decoded, MCRYPT_MODE_ECB));
        return $decrypted;
    }


}