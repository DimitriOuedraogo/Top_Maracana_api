<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/' . config('services.firebase.credentials', 'firebase-credentials.json'));
        $this->projectId       = config('services.firebase.project_id', '');
    }

    public function send(string $fcmToken, string $title, string $body, array $data = []): void
    {
        if (empty($fcmToken) || empty($this->projectId)) {
            return;
        }

        try {
            $accessToken = $this->getAccessToken();

            $payload = [
                'message' => [
                    'token'        => $fcmToken,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data'         => array_map('strval', $data),
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);

            if ($response->failed()) {
                Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('FCM error: ' . $e->getMessage());
        }
    }

    private function getAccessToken(): string
    {
        $scopes      = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $this->credentialsPath);
        $token       = $credentials->fetchAuthToken();

        return $token['access_token'];
    }
}
