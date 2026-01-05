<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlaskService
{
    protected $baseUrl;

    protected $secret;

    public function __construct()
    {
        // Load config once. Ensure config/services.php is set up.
        $this->baseUrl = rtrim(config('services.flask.url'), '/');
        $this->secret = config('services.flask.secret');
    }

    /**
     * Internal helper to create the HTTP client with default headers.
     *
     * @param  int  $timeout  Seconds to wait before failing
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function client(int $timeout = 30)
    {
        return Http::withHeaders([
            'X-Internal-Secret' => $this->secret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout($timeout);
    }

    /**
     * 1. ChatBot: Ask the AI Assistant a question.
     */
    public function askAssistant(string $message): string
    {
        $endpoint = $this->baseUrl.'/api/ollama/website-chat';

        try {
            $response = $this->client(130)->post($endpoint, [
                'query' => $message,
            ]);

            if ($response->successful()) {
                return $response->json()['reply'] ?? 'Empty response from AI.';
            }

            $this->logError('ChatBot', $response);
            throw new Exception('AI Service unavailable.');
        } catch (Exception $e) {
            // Re-throw to let Controller handle the 503 response
            throw $e;
        }
    }

    /**
     * 2. Roadmap: Generate a learning path.
     * Note: High timeout (120s) for complex generation.
     */
    public function generateRoadmap(array $payload): array
    {
        $endpoint = $this->baseUrl.'/api/laravel/generate-roadmap';

        try {
            $response = $this->client(120)->post($endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            $this->logError('Roadmap Gen', $response);
            throw new Exception('Failed to generate roadmap data.');
        } catch (Exception $e) {
            Log::error('FlaskService Roadmap Exception: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * 3. System Health: Check status.
     * Returns null on failure so the Controller can use defaults.
     */
    public function getHealthStatus(string $modelFile = 'devnexus.pkl'): ?array
    {
        $endpoint = $this->baseUrl.'/api/monitor/health';

        try {
            $response = $this->client(5)->get($endpoint, [
                'model_file' => $modelFile,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            // Health checks should fail silently in the service
            // so the dashboard can display "Outage" instead of crashing.
        }

        return null;
    }

    /**
     * Helper to log standardized errors.
     */
    protected function logError(string $context, $response)
    {
        Log::error("FlaskService [$context] Error: ".$response->status().' - '.$response->body());
    }
}
