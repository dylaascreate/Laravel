<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'email'=> ['required', 'email'],
        'password'=> ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return response()->json(['message'=> 'Login successful']);
    }

    return response()->json(['message'=>'Invalid credentials'],401);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request){
    Auth::guard('web')->logout();
    $request->session()->Invalidate();
    $request->session()->regenerateToken();
    return response()->noContent();
});

Route::middleware('auth:sanctum')->group(function () {
    // ENDPOINT TO ISSUE TOKEN FOR FLASK
    Route::get('/create-api-token', function (Request $request) {
        // Create a token named "flask-token"
        $token = $request->user()->createToken('flask-token');

        // Return the plain text token (you only see this once!)
        return response()->json([
            'token' => $token->plainTextToken
        ]);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- ADD DASHBOARD ROUTES LIKE THIS ---
    Route::get('/dashboard', function () {
        return response()->json([
            'users_count' => 100,
            'sales_total' => 5000,
            'recent_activity' => ['Logged in', 'Updated profile']
        ]);
    });

    // --- THE PROXY ROUTE ---
    Route::post('/ai-prediction', function (Request $request) {

        // 1. Get the Flask URL from .env
        $flaskUrl = env('FLASK_API_URL') . '/predict';

        // 2. Make the HTTP request to Flask (Server-to-Server)
        // We send a secret header so Flask knows it's us.
        $response = Http::withHeaders([
            'X-Internal-Secret' => env('FLASK_API_SECRET')
        ])->post($flaskUrl, [
            // Pass data from Vue -> Laravel -> Flask
            'input_data' => $request->input('data')
        ]);

        // 3. Handle Errors
        if ($response->failed()) {
        // CHANGED: Return the actual error body from Flask, and the actual status code
        return response()->json([
            'error' => 'Flask Error',
            'details' => $response->json() // <--- This will show us why Flask failed
        ], $response->status());
    }
        // 4. Return Flask's answer back to Vue
        return $response->json();
    });
});
