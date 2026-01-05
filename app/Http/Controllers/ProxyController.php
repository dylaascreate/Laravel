<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function handleFlaskConnection(Request $request)
    {
        // 1. Get URL from env
        $flaskUrl = env('FLASK_API_URL').'/api/laravel/conn';

        // 2. Make request to Flask
        $response = Http::withHeaders([
            'X-Internal-Secret' => env('FLASK_API_SECRET'),
        ])->post($flaskUrl, [
            'input_data' => $request->input('data'),
        ]);

        // 3. Handle Errors
        if ($response->failed()) {
            return response()->json([
                'error' => 'Flask Error',
                'details' => $response->json(),
            ], $response->status());
        }

        // 4. Return success
        return $response->json();
    }
}
