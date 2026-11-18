<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class JanataPayClient
{
    public function __construct(private readonly GatewayCrypto $crypto) {}

    public function tokenize(int $amountCents, string $description, string $referenceId, array $accountList = []): array
    {
        $payload = [
            'merchantUid' => $this->setting('merchant_uid'),
            'amount' => Money::jsonNumber($amountCents),
            'currency' => 'BDT',
            'description' => $description,
            'referenceId' => $referenceId,
        ];
        if ($accountList !== []) {
            $payload['accountList'] = $accountList;
        }

        $result = $this->sessionCall('/jbagg/api/transaction/tokenize', $payload);
        // \Illuminate\Support\Facades\Log::info('JanataPay Tokenize Response', [
        //     'referenceId' => $referenceId,
        //     'url' => $result['url'] ?? null,
        //     'transactionToken_received' => ! empty($result['transactionToken']),
        // ]);
        if (empty($result['transactionToken']) || empty($result['url'])) {
            throw new GatewayException('JanataPay did not return a payment token and URL.');
        }

        return $result;
    }

    public function verify(string $referenceId, string $transactionToken): array
    {
        return $this->sessionCall('/jbagg/api/transaction/verify', [
            'merchantUid' => $this->setting('merchant_uid'),
            'referenceId' => $referenceId,
            'transactionToken' => $transactionToken,
        ]);
    }

    private function sessionCall(string $path, array $payload): array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $session = $this->session($attempt > 0);
            $payload['accessToken'] = $session['accessToken'];
            try {
                return $this->encryptedCall($path, [
                    'data' => $this->crypto->encrypt($payload, $session['aesKey']),
                    'accessToken' => $session['accessToken'],
                ], $session['aesKey']);
            } catch (GatewayException $error) {
                if ($error->httpStatus !== 401 || $attempt > 0) {
                    throw $error;
                }
            }
        }
        throw new GatewayException('JanataPay access token could not be refreshed.');
    }

    private function session(bool $refresh = false): array
    {
        $key = 'janatapay-session-'.hash('sha256', $this->setting('merchant_uid'));
        if (! $refresh && is_array($cached = Cache::get($key))) {
            return $cached;
        }

        $this->requireCredentials();
        $aesKey = base64_encode(random_bytes(32));
        $response = $this->encryptedCall('/jbagg/api/auth', [
            'merchant' => base64_encode($this->setting('merchant_uid')),
            'key' => $this->crypto->encryptKey($aesKey, $this->publicKey()),
            'data' => $this->crypto->encrypt([
                'merchantUid' => $this->setting('merchant_uid'),
                'merchantUsername' => $this->setting('merchant_username'),
                'merchantPassword' => $this->setting('merchant_password'),
            ], $aesKey),
        ], $aesKey);
        if (empty($response['accessToken']) || ! is_string($response['accessToken'])) {
            throw new GatewayException('Authentication did not return an access token.');
        }

        $session = ['accessToken' => $response['accessToken'], 'aesKey' => $aesKey];
        $expiresIn = max(1, (int) ($response['expiresIn'] ?? 3600) - 30);
        Cache::put($key, $session, $expiresIn);

        return $session;
    }

    private function publicKey(): string
    {
        if ($key = $this->setting('public_key')) {
            return $key;
        }
        try {
            $response = Http::timeout(10)->get($this->setting('base_url').'/sandbox-public-key');
        } catch (ConnectionException $error) {
            throw new GatewayException('Cannot fetch the JanataPay public key.');
        }
        if (! $response->successful() || trim($response->body()) === '') {
            throw new GatewayException('JanataPay public key is unavailable.');
        }

        return trim($response->body());
    }

    private function encryptedCall(string $path, array $request, string $aesKey): array
    {
        try {
            $response = Http::acceptJson()->asJson()->connectTimeout(10)->timeout(30)
                ->post($this->setting('base_url').$path, $request);
        } catch (ConnectionException $error) {
            throw new GatewayException('Cannot connect to JanataPay.');
        }

        $envelope = $response->json();
        if (! is_array($envelope)) {
            throw new GatewayException('JanataPay returned invalid JSON (HTTP '.$response->status().').', $response->status());
        }
        $data = is_string($envelope['data'] ?? null)
            ? $this->crypto->decrypt($envelope['data'], $aesKey)
            : null;
        $message = $data['statusMessage'] ?? $envelope['statusMessage'] ?? 'JanataPay request failed.';
        $businessStatus = (int) ($data['statusCode'] ?? 200);
        if ($response->failed() || (int) ($envelope['statusCode'] ?? 200) >= 400 || $businessStatus >= 400) {
            throw new GatewayException($message, $response->status());
        }
        if (! is_array($data)) {
            throw new GatewayException('JanataPay response did not contain encrypted data.', $response->status());
        }

        return $data;
    }

    private function requireCredentials(): void
    {
        foreach (['merchant_uid', 'merchant_username', 'merchant_password'] as $field) {
            if ($this->setting($field) === '') {
                throw new GatewayException('Set the LaravelDemo merchant credentials in .env before paying.');
            }
        }
    }

    private function setting(string $key): string
    {
        return (string) config('janatapay.'.$key, '');
    }
}
