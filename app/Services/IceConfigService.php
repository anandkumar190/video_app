<?php

namespace App\Services;

class IceConfigService
{
    /**
     * Get ICE server configuration for WebRTC connections
     *
     * @return array
     */
    public function getIceServers(): array
    {
        $iceServers = [];

        // Add STUN servers
        $stunUrls = $this->parseServerUrls(config('services.webrtc.stun.urls'));
        foreach ($stunUrls as $url) {
            $iceServers[] = [
                'urls' => $url
            ];
        }

        // Add TURN servers with credentials
        $turnUrls = $this->parseServerUrls(config('services.webrtc.turn.urls'));
        $turnUsername = config('services.webrtc.turn.username');
        $turnCredential = config('services.webrtc.turn.credential');

        if (!empty($turnUrls) && $turnUsername && $turnCredential) {
            foreach ($turnUrls as $url) {
                $iceServers[] = [
                    'urls' => $url,
                    'username' => $turnUsername,
                    'credential' => $turnCredential
                ];
            }
        }

        // Add Google STUN servers as fallback if no STUN servers configured
        if (empty($iceServers)) {
            $iceServers = [
                ['urls' => 'stun:stun.l.google.com:19302'],
                ['urls' => 'stun:stun1.l.google.com:19302'],
                ['urls' => 'stun:stun2.l.google.com:19302']
            ];
        }

        return $iceServers;
    }

    /**
     * Get complete WebRTC configuration
     *
     * @return array
     */
    public function getWebRtcConfig(): array
    {
        return [
            'iceServers' => $this->getIceServers(),
            'iceTransportPolicy' => 'all', // 'all' or 'relay'
            'iceCandidatePoolSize' => 10,
        ];
    }

    /**
     * Parse comma-separated server URLs
     *
     * @param string|null $urlString
     * @return array
     */
    private function parseServerUrls(?string $urlString): array
    {
        if (empty($urlString)) {
            return [];
        }

        $urls = array_map('trim', explode(',', $urlString));
        return array_filter($urls, function ($url) {
            return !empty($url) && $this->isValidServerUrl($url);
        });
    }

    /**
     * Validate server URL format
     *
     * @param string $url
     * @return bool
     */
    private function isValidServerUrl(string $url): bool
    {
        return preg_match('/^(stun|turn|turns):[a-zA-Z0-9.-]+:[0-9]+$/', $url) === 1;
    }

    /**
     * Get ICE server statistics for monitoring
     *
     * @return array
     */
    public function getIceServerStats(): array
    {
        $iceServers = $this->getIceServers();

        return [
            'total_servers' => count($iceServers),
            'stun_servers' => count(array_filter($iceServers, function ($server) {
                return strpos($server['urls'], 'stun:') === 0;
            })),
            'turn_servers' => count(array_filter($iceServers, function ($server) {
                return strpos($server['urls'], 'turn') === 0;
            })),
            'has_credentials' => !empty(config('services.webrtc.turn.username')),
            'servers' => array_column($iceServers, 'urls')
        ];
    }
}