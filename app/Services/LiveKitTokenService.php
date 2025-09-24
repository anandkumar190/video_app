<?php

namespace App\Services;

use Exception;

class LiveKitTokenService implements TokenServiceInterface
{
    private string $apiKey;
    private string $apiSecret;
    private string $serverUrl;

    public function __construct()
    {
        $this->apiKey = config('services.livekit.api_key');
        $this->apiSecret = config('services.livekit.api_secret');
        $this->serverUrl = config('services.livekit.url');

        if (!$this->apiKey || !$this->apiSecret) {
            throw new Exception('LiveKit API key and secret must be configured');
        }
    }

    /**
     * Generate an access token for joining a LiveKit room
     */
    public function generateAccessToken(
        string $identity,
        string $roomName,
        array $permissions = ['join', 'subscribe'],
        int $ttl = 3600
    ): string {
        $now = time();
        $exp = $now + $ttl;

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'iss' => $this->apiKey,
            'sub' => $identity,
            'iat' => $now,
            'exp' => $exp,
            'video' => [
                'room' => $roomName,
                'roomJoin' => in_array('join', $permissions),
                'canSubscribe' => in_array('subscribe', $permissions),
                'canPublish' => in_array('publish', $permissions),
                'canPublishData' => in_array('publish_data', $permissions, true),
                'hidden' => false,
                'recorder' => false
            ]
        ];

        if (in_array('admin', $permissions)) {
            $payload['video']['roomAdmin'] = true;
            $payload['video']['roomList'] = true;
            $payload['video']['roomCreate'] = true;
        }

        return $this->encodeJWT($header, $payload);
    }

    /**
     * Validate and decode a LiveKit token
     */
    public function validateToken(string $token): ?array
    {
        try {
            return $this->decodeJWT($token);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Encode JWT token using HMAC SHA256
     */
    private function encodeJWT(array $header, array $payload): string
    {
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $data = $headerEncoded . '.' . $payloadEncoded;
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $data, $this->apiSecret, true));

        return $data . '.' . $signature;
    }

    /**
     * Decode JWT token
     */
    private function decodeJWT(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $header = json_decode($this->base64UrlDecode($headerEncoded), true);
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        if (!$header || !$payload) {
            throw new Exception('Invalid token data');
        }

        $data = $headerEncoded . '.' . $payloadEncoded;
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $data, $this->apiSecret, true));

        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            throw new Exception('Invalid token signature');
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        return $payload;
    }

    /**
     * Base64 URL-safe encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe decode
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Get permissions for a user role
     */
    public function getPermissionsForRole(string $role): array
    {
        return match ($role) {
            'host' => ['join', 'subscribe', 'publish', 'publish_data', 'admin'],
            'guest' => ['join', 'subscribe'],
            default => ['join', 'subscribe']
        };
    }
}