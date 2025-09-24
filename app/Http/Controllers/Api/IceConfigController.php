<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IceConfigService;

class IceConfigController extends Controller
{
    private IceConfigService $iceConfigService;

    public function __construct(IceConfigService $iceConfigService)
    {
        $this->iceConfigService = $iceConfigService;
    }

    /**
     * Get ICE server configuration for WebRTC
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIceConfig()
    {
        try {
            $webrtcConfig = $this->iceConfigService->getWebRtcConfig();

            return response()->json($webrtcConfig);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get ICE configuration',
                'error' => $e->getMessage(),
                'iceServers' => [
                    ['urls' => 'stun:stun.l.google.com:19302']
                ]
            ], 500);
        }
    }

    /**
     * Get ICE server statistics for debugging
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIceStats()
    {
        try {
            $stats = $this->iceConfigService->getIceServerStats();

            return response()->json([
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get ICE statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}