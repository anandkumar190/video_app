<?php

namespace App\Services;

interface TokenServiceInterface
{
    /**
     * Generate an access token for joining a room
     *
     * @param string $identity User identifier (user ID + role)
     * @param string $roomName Room name/slug
     * @param array $permissions Permissions array (join, subscribe, publish, etc.)
     * @param int $ttl Token time-to-live in seconds (default: 1 hour)
     * @return string Generated JWT token
     */
    public function generateAccessToken(
        string $identity,
        string $roomName,
        array $permissions = ['join', 'subscribe'],
        int $ttl = 3600
    ): string;

    /**
     * Validate and decode a token
     *
     * @param string $token JWT token to validate
     * @return array|null Decoded token payload or null if invalid
     */
    public function validateToken(string $token): ?array;
}