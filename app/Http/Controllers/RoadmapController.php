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
{
    public function generate(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

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
            $aiData = $this->callFlaskApi($payload);
            $roadmap = $this->saveRoadmap($aiData, $request, $targetCareer);

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

        return $aiResponse['roadmap'];
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
                'type' => $request->input('type', 'general'),
                'title' => $aiData['title'],
                'career_role' => $targetCareer,
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

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $roadmap = Roadmap::with('phases.tasks')->where('user_id', Auth::id())->findOrFail($id); // Wrap in 'data' to match your frontend's expectations

        return response()->json(['data' => $roadmap]);
    }
}
