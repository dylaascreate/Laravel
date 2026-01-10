<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\SystemHealthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\RoadmapTaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// PUBLIC ROUTES (No Login Required)
// =========================================================================

// --- Auth: Registration & Login ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Add these to your Public Routes section
// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// --- System & Health ---
Route::get('/system-status', [SystemHealthController::class, 'systemStatus']);

// --- ChatBot (AI) ---
// Note: Kept public based on original file. Move to protected if user context is needed.
Route::post('/chat/send', [ChatBotController::class, 'sendMessage']);


// =========================================================================
// PROTECTED ROUTES (Requires 'auth:sanctum' Token)
// =========================================================================

Route::middleware('auth:sanctum')->group(function () {

    // --- Authentication & User Management ---
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/create-api-token', [AuthController::class, 'createApiToken']);

    // --- Career Management ---
    Route::get('/careers', [CareerController::class, 'index']);      // List careers
    Route::post('/user/career', [CareerController::class, 'update']); // Update user's career

    // --- Dashboard & System Proxy ---
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- Roadmap Engine ---
    Route::post('/roadmaps/generate', [RoadmapController::class, 'generate']);
    // Route::get('/roadmap/{id}', [RoadmapController::class, 'show']);    // [FIXED] Now inside auth middleware
    // Route::delete('/roadmap/{id}', [RoadmapController::class, 'destroy']); // [FIXED] Now inside auth middleware

    Route::apiResource('roadmaps', RoadmapController::class);
    // If this line is missing, you will get a Network Error (404)
    Route::patch('/roadmaps/{roadmap}/progress', [RoadmapController::class, 'updateProgress']);
    Route::post('/roadmaps/{roadmap}/reset', [RoadmapController::class, 'reset']);

    // Add this line in routes/api.php
    Route::post('/phases/{phase}/tasks', [RoadmapTaskController::class, 'store']);

    // 1. EDIT DETAILS (Title/Subtitle) -> Uses PUT
    // Front-end must call: axios.put('/api/tasks/1', { title: ... })
    Route::put('/tasks/{id}', [RoadmapTaskController::class, 'update']);

    // 2. TOGGLE STATUS (Checkbox) -> Uses PATCH (Your preferred line)
    // Front-end must call: axios.patch('/api/tasks/1', { completed: ... })
    Route::patch('/tasks/{task}', [RoadmapTaskController::class, 'updateStatus']);

    // Admin: Global Skill Management
    // Add middleware here to check for 'admin' role if needed
    Route::apiResource('skills', SkillController::class);

    // Student: Personal Skill Matrix
    Route::get('/student/skills', [SkillController::class, 'userMatrix']);
    Route::post('/student/skills/{skill}', [SkillController::class, 'attachUserSkill']);
    Route::delete('/student/skills/{skill}', [SkillController::class, 'detachUserSkill']);

});
