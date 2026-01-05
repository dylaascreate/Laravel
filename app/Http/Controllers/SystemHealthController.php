<?php

namespace App\Http\Controllers;

use App\Services\FlaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    public function systemStatus(FlaskService $flaskService)
    {
        $startTime = microtime(true);

        // 1. Check PostgreSQL (Existing Logic)
        try {
            DB::connection()->getPdo();
            $dbStatus = 'operational';
            $dbConnections = DB::select('select count(*) as count from pg_stat_activity')[0]->count ?? 0;
        } catch (\Exception $e) {
            $dbStatus = 'outage';
            $dbConnections = 0;
        }

        // 2. Fetch AI Status from Flask (Internal Request)
        // Initialize default state (Updated to include dependencies & services structure)
        $defaults = [
            'flask' => ['status' => 'outage', 'connections' => 0],
            'ollama' => ['status' => 'unknown', 'model' => 'unknown', 'latency' => 0],
            'custom_model' => ['status' => 'unknown', 'file' => 'devnexus.pkl', 'size' => '0 MB', 'integrity' => 'unknown', 'latency' => 0],
            'dependencies' => [], // <--- Added default for robustness
            'services' => [],      // <--- Default empty array is fine, frontend handles "unknown"
        ];
        // Service returns null on failure, allowing easy fallback
        $flaskData = $flaskService->getHealthStatus() ?? $defaults;

        // Merge defaults in case partial data is returned
        $flaskData = array_merge($defaults, $flaskData);

        // 3. Calculate Laravel Latency
        $latency = round((microtime(true) - $startTime) * 1000);

        // 4. Construct Final JSON
        return response()->json([
            'core' => [
                'laravel' => [
                    'name' => 'Laravel Core API',
                    'version' => app()->version(),
                    'php' => phpversion(),
                    'status' => 'operational',
                    'latency' => $latency,
                    'queue' => 'Idle',
                ],
                'postgres' => [
                    'name' => 'PostgreSQL DB',
                    'version' => '17.4',
                    'status' => $dbStatus,
                    'connections' => $dbConnections,
                    'latency' => 5,
                    'size' => '1.2 GB',
                ],
                'flask' => $flaskData['flask'] ?? ['status' => 'outage'],
                'ollama' => $flaskData['ollama'] ?? ['status' => 'outage'],
                'custom_model' => $flaskData['custom_model'] ?? $flaskData['custom_model'],

                // Optional: Pass dependencies if you want to debug missing files in frontend later
                'dependencies' => $flaskData['dependencies'] ?? [],
            ],
            // Pass the services list directly to Frontend
            'services' => $flaskData['services'] ?? [],
        ]);
    }
}
