<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\crypt;

/**
 * Class RSACrypt.
 */
class RSACrypt
{
    /**
     * 私钥加密.
     */
    public function encryptByPrivateKey(string $data, string $privateKey): string
    {
        $pi_key = openssl_pkey_get_private($privateKey);
        $encrypted = '';
        openssl_private_encrypt($data, $encrypted, $pi_key, OPENSSL_PKCS1_PADDING); //私钥加密
        //加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return self::urlsafeB64encode($encrypted);
    }

    /**
     * 公钥解密.
     */
    public function decryptByPublicKey(string $data, string $publicKey): string
    {
        $pu_key = openssl_pkey_get_public($publicKey);
        $decrypted = '';
        $data = self::urlsafeB64decode($data);
        openssl_public_decrypt($data, $decrypted, $pu_key); //公钥解密
        return $decrypted;
    }

    /**
     * 公钥加密.
     */
    public function encryptByPublicKey(string $data, string $publicKey): string
    {
        $pu_key = openssl_pkey_get_public($publicKey);
        $encrypted = '';
        openssl_public_encrypt($data, $encrypted, $pu_key, OPENSSL_PKCS1_PADDING); //公钥加密
        //加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return self::urlsafeB64encode($encrypted);
    }

    /**
     * 私钥解密.
     */
    public function decryptByPrivateKey(string $data, string $privateKey): string
    {
        $pi_key = openssl_pkey_get_private($privateKey);
        $decrypted = '';
        $data = self::urlsafeB64decode($data);
        openssl_private_decrypt($data, $decrypted, $pi_key); //私钥解密
        return $decrypted;
    }

    /**
     * 安全的b64encode.
     * @param $string
     * @return mixed|string
     */
    private static function urlsafeB64encode($string): string
    {
        $data = base64_encode($string);
        return str_replace(['+', '/', '='], ['-', '_', '@'], $data);
    }

    /**
     * 安全的b64decode.
     * @param $string
     */
    private static function urlsafeB64decode($string): string
    {
        $data = str_replace(['-', '_', '@'], ['+', '/', '='], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}
