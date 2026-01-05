<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\SystemHealthController;
use Illuminate\Support\Facades\Route;

// System Health Check
Route::get('/system-status', [SystemHealthController::class, 'systemStatus']);

// --- Public Routes ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- Protected Routes (Require Login) ---
Route::middleware('auth:sanctum')->group(function () {

    // Auth & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/create-api-token', [AuthController::class, 'createApiToken']);

    // Features
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/flask-conn', [ProxyController::class, 'handleFlaskConnection']);

});

Route::middleware('auth:sanctum')->post('/generate-roadmap', [RoadmapController::class, 'generate']);
Route::get('/roadmap/{id}', [RoadmapController::class, 'show']);
Route::delete('/roadmap/{id}', [RoadmapController::class, 'destroy']);
// Route::patch('/tasks/{id}/toggle', [RoadmapController::class, 'toggleTask']);

Route::middleware('auth:sanctum')->group(function () {

    // Get list of careers (for dropdowns)
    Route::get('/careers', [CareerController::class, 'index']);

    // Set user's career
    Route::post('/user/career', [CareerController::class, 'update']);

});

Route::post('/chat/send', [ChatBotController::class, 'sendMessage']);
