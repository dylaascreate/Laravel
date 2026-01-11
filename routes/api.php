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
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AiFeatureController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. PUBLIC ROUTES
// =========================================================================

Route::prefix('ai')->group(function () {
    Route::post('/score-cv', [AiFeatureController::class, 'scoreCv']);
    Route::post('/suggest-skills', [AiFeatureController::class, 'suggestSkills']);
    Route::post('/career-roadmap', [AiFeatureController::class, 'generateRoadmap']);
    Route::post('/skill-expand', [AiFeatureController::class, 'expandSkill']);
    Route::post('/quiz', [AiFeatureController::class, 'generateQuiz']);
    Route::post('/grade-quiz', [AiFeatureController::class, 'gradeQuiz']);
});

// --- Authentication & User Management ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/create-api-token', [AuthController::class, 'createApiToken']);

    // [NEW] Profile & Security Routes
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);


Route::get('/system-status', [SystemHealthController::class, 'systemStatus']);
Route::post('/chat/send', [ChatBotController::class, 'sendMessage']);
Route::post('/sync-courses', [CourseController::class, 'sync']);

// =========================================================================
// 2. PROTECTED ROUTES
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Common ---
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/create-api-token', [AuthController::class, 'createApiToken']);
    Route::get('/careers', [CareerController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // =====================================================================
    // A. ADMIN ROUTES (Only Admin)
    // =====================================================================
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('skills', SkillController::class);

        // This generates index, store, show, update, destroy for careers
        Route::apiResource('careers', CareerController::class);

        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    });

    // =====================================================================
    // B. STUDENT ROUTES (Student OR Admin)
    // =====================================================================
    Route::middleware(['role:student|admin'])->group(function () {

        // --- Personal Skill Matrix ---
        Route::get('/student/skills', [SkillController::class, 'userMatrix']);
        Route::post('/student/skills/{skill}', [SkillController::class, 'attachUserSkill']);
        Route::delete('/student/skills/{skill}', [SkillController::class, 'detachUserSkill']);

        Route::apiResource('student/projects', ProjectController::class)->except(['show']);

        // --- Career Selection ---
        // UPDATED: Points to updateUserCareer to avoid conflict with admin 'update'
        Route::post('/career', [CareerController::class, 'updateUserCareer']);

        Route::post('/careers/recommend', [CareerController::class, 'recommend']);

        // --- Roadmap Generation ---
        Route::post('/roadmaps/generate', [RoadmapController::class, 'generate']);

        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/student/courses', [CourseController::class, 'userCourses']);
        Route::post('/student/courses', [CourseController::class, 'enroll']);
        Route::put('/student/courses/{course}', [CourseController::class, 'updateProgress']);
        Route::delete('/student/courses/{course}', [CourseController::class, 'drop']);
    });

    // =====================================================================
    // C. SHARED / GENERAL ROADMAP ROUTES
    // =====================================================================
    Route::apiResource('roadmaps', RoadmapController::class);
    Route::patch('/roadmaps/{roadmap}/progress', [RoadmapController::class, 'updateProgress']);
    Route::post('/roadmaps/{roadmap}/reset', [RoadmapController::class, 'reset']);

    Route::post('/phases/{phase}/tasks', [RoadmapTaskController::class, 'store']);
    Route::put('/tasks/{id}', [RoadmapTaskController::class, 'update']);
    Route::patch('/tasks/{task}', [RoadmapTaskController::class, 'updateStatus']);
});
