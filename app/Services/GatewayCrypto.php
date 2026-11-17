<?php

namespace App\Services;

use phpseclib4\Crypt\PublicKeyLoader;
use phpseclib4\Crypt\RSA;
use Throwable;

class GatewayCrypto
{
    public function encrypt(array $payload, string $aesKeyBase64): string
    {
        $key = $this->keyBytes($aesKeyBase64);
        $iv = random_bytes(12);
        $tag = '';
        $plain = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $ciphertext = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($ciphertext === false) {
            throw new GatewayException('Could not encrypt the JanataPay request.');
        }

        return base64_encode($iv.$ciphertext.$tag);
    }

    public function decrypt(string $payloadBase64, string $aesKeyBase64): array
    {
        $raw = base64_decode($payloadBase64, true);
        if ($raw === false || strlen($raw) < 29) {
            throw new GatewayException('JanataPay returned an invalid encrypted response.');
        }
        $plain = openssl_decrypt(
            substr($raw, 12, -16),
            'aes-256-gcm',
            $this->keyBytes($aesKeyBase64),
            OPENSSL_RAW_DATA,
            substr($raw, 0, 12),
            substr($raw, -16),
        );
        if ($plain === false) {
            throw new GatewayException('Could not decrypt the JanataPay response.');
        }
        try {
            $decoded = json_decode($plain, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $error) {
            throw new GatewayException('JanataPay returned invalid decrypted JSON.');
        }
        if (! is_array($decoded)) {
            throw new GatewayException('JanataPay returned an invalid decrypted response.');
        }

        return $decoded;
    }

    public function encryptKey(string $aesKeyBase64, string $rawPublicKey): string
    {
        $this->keyBytes($aesKeyBase64);
        $rawPublicKey = preg_replace('/\s+/', '', $rawPublicKey);
        $pem = "-----BEGIN PUBLIC KEY-----\n".chunk_split($rawPublicKey, 64, "\n")."-----END PUBLIC KEY-----\n";
        try {
            $key = PublicKeyLoader::load($pem)
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha256')
                ->withMGFHash('sha256');

            return base64_encode($key->encrypt($aesKeyBase64));
        } catch (Throwable $error) {
            throw new GatewayException('JanataPay public key is invalid.');
        }
    }

    private function keyBytes(string $aesKeyBase64): string
    {
        $key = base64_decode($aesKeyBase64, true);
        if ($key === false || strlen($key) !== 32) {
            throw new GatewayException('Invalid AES-256 key.');
        }

        return $key;
    }
}
