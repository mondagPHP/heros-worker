<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\crypt;

/**
 * Class RSACryptBigData.
 */
class RSACryptBigData
{
    /**
     * 公钥加密.
     */
    public function encryptByPublicKeyData(string $data, string $publicKey): string
    {
        $RSACrypt = new RSACrypt();
        $cryptRes = '';
        for ($i = 0; $i < ((strlen($data) - strlen($data) % 117) / 117 + 1); ++$i) {
            $cryptRes = $cryptRes . ($RSACrypt->encryptByPublicKey(mb_strcut($data, $i * 117, 117, 'utf-8'), $publicKey));
        }
        return $cryptRes;
    }

    /**
     * 私钥解密.
     * @param $data
     */
    public function decryptByPrivateKeyData(string $data, string $privateKey): string
    {
        $RSACrypt = new RSACrypt();
        $decryptRes = '';
        $datas = explode('@', $data);
        foreach ($datas ?? [] as $value) {
            $decryptRes = $decryptRes . $RSACrypt->decryptByPrivateKey($value, $privateKey);
        }
        return $decryptRes;
    }

    /**
     * 私钥加密.
     * @param $data
     */
    public function encryptByPrivateKeyData(string $data, string $privateKey): string
    {
        $RSACrypt = new RSACrypt();
        $cryptRes = '';
        for ($i = 0; $i < ((strlen($data) - strlen($data) % 117) / 117 + 1); ++$i) {
            $cryptRes = $cryptRes . ($RSACrypt->encryptByPrivateKey(mb_strcut($data, $i * 117, 117, 'utf-8'), $privateKey));
        }
        return $cryptRes;
    }

    /**
     * 公钥解密.
     */
    public function decryptByPublicKeyData(string $data, string $publicKey): string
    {
        $RSACrypt = new RSACrypt();
        $decryptRes = '';
        $datas = explode('@', $data);
        foreach ($datas as $value) {
            $decryptRes = $decryptRes . $RSACrypt->decryptByPublicKey($value, $publicKey);
        }
        return $decryptRes;
    }
}
