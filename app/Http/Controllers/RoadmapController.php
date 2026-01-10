<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapPhase;
use App\Models\RoadmapTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RoadmapController extends Controller
{/**
     * Display a listing of the user's roadmaps.
     * Endpoint: GET /api/roadmaps
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->roadmaps();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $roadmaps = $query
            ->with(['phases']) // <--- ADD THIS: Loads phases and their 'skills' column
            ->withCount('tasks')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $roadmaps]);
    }

    /**
     * Store a newly created roadmap in storage.
     * Endpoint: POST /api/roadmaps
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:general,academic',
            'career_goal' => 'nullable|string|max:100', // e.g., 'Frontend Developer'
        ]);

        // Create roadmap linked to authenticated user
        $roadmap = Auth::user()->roadmaps()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type'        => $validated['type'],
            'career_goal' => $validated['career_goal'] ?? 'Software Engineer',
            'status'      => 'Active',
            'progress'    => 0
        ]);

        return response()->json([
            'message' => 'Roadmap initialized successfully',
            'data'    => $roadmap
        ], 201);
    }

    /**
     * Update the specified roadmap.
     * Endpoint: PUT /api/roadmaps/{id}
     */
    public function update(Request $request, $id)
    {
        $roadmap = Auth::user()->roadmaps()->findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'sometimes|in:active,completed,archived', // ✅ Use lowercase
            'progress'    => 'sometimes|integer|min:0|max:100'
        ]);

        $roadmap->update($validated);

        return response()->json([
            'message' => 'Roadmap updated successfully',
            'data'    => $roadmap
        ]);
    }

    /**
     * Remove the specified roadmap from storage.
     * Endpoint: DELETE /api/roadmaps/{id}
     */
    public function destroy($id)
    {
        $roadmap = Auth::user()->roadmaps()->findOrFail($id);

        $roadmap->delete();

        return response()->json([
            'message' => 'Roadmap deleted successfully'
        ]);
    }

    // ==========================================
    // EXTRA: AI GENERATION ENDPOINT
    // ==========================================

    /**
     * Handle AI Generation request.
     * Endpoint: POST /api/roadmaps/generate
     */
    public function generate(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        set_time_limit(600);
        // Fetch the name from the relationship, or fallback
        $targetCareer = $user->career ? $user->career->name : 'Software Engineer';
        // Get array of skill names from skill_user table
        $skills = $user->skills()->pluck('name')->toArray();
        // Get course history with codes and status
        $userCourses = $user->courses()->select('course_code', 'status')->get()->toArray();

        // Validate User Input
        $request->validate([
            'query' => 'required|string',
            'level' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $payload = $this->preparePayload($request, $targetCareer, $skills, $userCourses);

        try {
            // 1. Get the FULL response (Fix the callFlaskApi method first as shown previously)
            $fullResponse = $this->callFlaskApi($payload);

            // 2. Isolate the AI roadmap data
            $aiRoadmapData = $fullResponse['roadmap'];

            // 3. CRITICAL: MAP DATA TO YOUR DB COLUMN
            // Flask sends: { "course_info": { "code": "CSC123" } }
            // Laravel DB needs: 'course_code'
            if (isset($fullResponse['course_info']['code'])) {
                $aiRoadmapData['course_code'] = $fullResponse['course_info']['code'];
            }

            // 4. Save to DB (This now has the correct 'course_code' key)
            $roadmap = $this->saveRoadmap($aiRoadmapData, $request, $targetCareer);

            return response()->json(['data' => $roadmap->load('phases.tasks')]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function preparePayload(Request $request, string $targetCareer, array $skills, array $userCourses): array
    {
        return [
            'type' => $request->input('type'),
            'query' => $request->input('query'),
            'level' => $request->input('level'),
            'targetCareer' => $targetCareer,
            'user_courses' => $userCourses,
            'skills' => $skills,
            'course_database' => [],
        ];
    }

    private function callFlaskApi(array $payload): array
    {
        $flaskUrl = config('services.flask.url').'/api/laravel/generate-roadmap';

        $response = Http::withHeaders([
            'X-Internal-Secret' => config('services.flask.secret'),
        ])->timeout(600)->post($flaskUrl, $payload);

        if ($response->failed()) {
            throw new \Exception('AI Generation Failed: '.$response->body());
        }

        $aiResponse = $response->json();
        if (! isset($aiResponse['roadmap'])) {
            throw new \Exception('Invalid AI response structure');
        }

        return $aiResponse;
    }

    private function saveRoadmap(array $aiData, Request $request, string $targetCareer): Roadmap
    {
        // Basic validation of AI data
        if (! isset($aiData['title'], $aiData['phases']) || ! is_array($aiData['phases'])) {
            throw new \Exception('Invalid roadmap data from AI');
        }

        return DB::transaction(function () use ($aiData, $request, $targetCareer) {
            $roadmap = Roadmap::create([
                'user_id' => Auth::id(),
                'type' => strtolower($request->input('type', 'general')),
                'title' => $aiData['title'],
                'career_goal' => $targetCareer,
                'level' => $aiData['level'] ?? 'Beginner',
                'estimate' => $aiData['estimate'] ?? null,
                'course_code' => $aiData['course_code'] ?? null,
                'status' => 'active',
            ]);

            foreach ($aiData['phases'] as $index => $phaseData) {
                if (! isset($phaseData['title'], $phaseData['description'], $phaseData['skills'], $phaseData['tasks'])) {
                    throw new \Exception('Invalid phase data');
                }

                $phase = RoadmapPhase::create([
                    'roadmap_id' => $roadmap->id,
                    'title' => $phaseData['title'],
                    'description' => $phaseData['description'],
                    'skills' => $phaseData['skills'],
                    'order_index' => $index + 1,
                ]);

                foreach ($phaseData['tasks'] as $taskIndex => $taskData) {
                    if (! isset($taskData['title'], $taskData['subtitle'])) {
                        throw new \Exception('Invalid task data');
                    }

                    RoadmapTask::create([
                        'phase_id' => $phase->id,
                        'title' => $taskData['title'] ?? 'Untitled Task',
                        'subtitle' => $taskData['subtitle'] ?? ($taskData['description'] ?? ''),
                        'completed' => false,
                        'order_index' => $taskIndex + 1,
                    ]);
                }
            }

            return $roadmap;
        });
    }

    public function show($id)
    {
        $roadmap = Auth::user()->roadmaps()
            ->with(['course','phases' => function($query) {
                $query->orderBy('order_index', 'asc'); // Ensure phases are in order
            }, 'phases.tasks' => function($query) {
                $query->orderBy('order_index', 'asc'); // Ensure tasks inside phases are in order
            }])
            ->findOrFail($id);

        return response()->json(['data' => $roadmap]);
    }

    public function updateProgress(Request $request, Roadmap $roadmap) {
    $request->validate([
        'progress' => 'required|integer|min:0|max:100'
    ]);

    $roadmap->update([
        'progress' => $request->progress
    ]);

    return response()->json(['message' => 'Progress updated']);
}
public function reset(Roadmap $roadmap) {
    return \DB::transaction(function () use ($roadmap) {
        // 1. Reset the parent Roadmap
        $roadmap->update([
            'status'   => 'active',
            'progress' => 0
        ]);

        // 2. Reset all Tasks linked to this roadmap via its phases
        // This is a high-performance query that updates all tasks at once
        \App\Models\RoadmapTask::whereHas('phase', function($query) use ($roadmap) {
            $query->where('roadmap_id', $roadmap->id);
        })->update(['completed' => false]);

        return response()->json(['message' => 'Protocol reset to active status.']);
    });
}
}
