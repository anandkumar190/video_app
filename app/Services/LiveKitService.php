<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\VideoGrant;
use Agence104\LiveKit\RoomServiceClient;

class LiveKitService
{
    protected $apiKey;
    protected $apiSecret;
    protected $wsUrl;
    protected $roomClient;

    public function __construct()
    {
        $this->apiKey = config('services.livekit.api_key');
        $this->apiSecret = config('services.livekit.api_secret');
        $this->wsUrl = config('services.livekit.ws_url');

        if ($this->apiKey && $this->apiSecret) {
            $this->roomClient = new RoomServiceClient(
                $this->wsUrl,
                $this->apiKey,
                $this->apiSecret
            );
        }
    }

    public function generateToken(string $roomName, string $identity, array $grants = []): string
    {
        $token = new AccessToken($this->apiKey, $this->apiSecret);
        $token->setIdentity($identity);
        $token->setTTL(3600); // 1 hour

        $videoGrant = new VideoGrant();
        $videoGrant->setRoom($roomName);
        $videoGrant->setCanPublish($grants['canPublish'] ?? false);
        $videoGrant->setCanSubscribe($grants['canSubscribe'] ?? true);
        $videoGrant->setCanPublishData($grants['canPublishData'] ?? false);

        $token->setGrant($videoGrant);

        return $token->toJwt();
    }

    public function createRoom(string $roomName, array $options = []): ?array
    {
        try {
            return $this->roomClient->createRoom([
                'name' => $roomName,
                'emptyTimeout' => $options['emptyTimeout'] ?? 300,
                'maxParticipants' => $options['maxParticipants'] ?? 100,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create LiveKit room: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteRoom(string $roomName): bool
    {
        try {
            $this->roomClient->deleteRoom(['room' => $roomName]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete LiveKit room: ' . $e->getMessage());
            return false;
        }
    }

    public function listRooms(): array
    {
        try {
            $rooms = $this->roomClient->listRooms();
            return $rooms['rooms'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Failed to list LiveKit rooms: ' . $e->getMessage());
            return [];
        }
    }

    public function getRoom(string $roomName): ?array
    {
        try {
            $rooms = $this->roomClient->listRooms(['names' => [$roomName]]);
            return $rooms['rooms'][0] ?? null;
        } catch (\Exception $e) {
            \Log::error('Failed to get LiveKit room: ' . $e->getMessage());
            return null;
        }
    }

    public function listParticipants(string $roomName): array
    {
        try {
            $participants = $this->roomClient->listParticipants(['room' => $roomName]);
            return $participants['participants'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Failed to list LiveKit participants: ' . $e->getMessage());
            return [];
        }
    }

    public function removeParticipant(string $roomName, string $identity): bool
    {
        try {
            $this->roomClient->removeParticipant([
                'room' => $roomName,
                'identity' => $identity
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to remove LiveKit participant: ' . $e->getMessage());
            return false;
        }
    }

    public function getWebSocketUrl(): string
    {
        return $this->wsUrl;
    }
}