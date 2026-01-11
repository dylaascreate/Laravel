<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiFeatureController extends Controller
{
    // CONFIGURATION: Point this to your local Flask app
    protected $flaskUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->flaskUrl = config('services.flask.url');
        $this->secretKey = config('services.flask.key');
    }

    /**
     * 1. ATS CV SCORER
     * Proxies the file upload to Flask.
     */
    public function scoreCv(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|file|mimes:pdf,txt|max:2048', // Max 2MB
            'job_description' => 'nullable|string'
        ]);

        try {
            // Get the file from the request
            $file = $request->file('cv_file');
            $jobDesc = $request->input('job_description', 'General Role');

            // Send multipart request to Flask
            $response = Http::withHeaders([
                'X-Internal-Secret' => $this->secretKey
            ])->attach(
                'cv_file',               // Field name expected by Flask
                file_get_contents($file), // File content
                $file->getClientOriginalName() // Filename
            )->post("{$this->flaskUrl}/api/ollama/score-cv", [
                'job_description' => $jobDesc
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error("ATS Scorer Failed: " . $e->getMessage());
            return response()->json(['error' => 'Failed to connect to AI Service'], 500);
        }
    }

    /**
     * 2. GENERATE ROADMAP
     * Sends user skills and goals to generate a learning path.
     */
    public function generateRoadmap(Request $request)
    {
        $validated = $request->validate([
            'targetCareer' => 'required|string',
            'skills' => 'nullable|array',
            'level' => 'nullable|string'
        ]);

        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $this->secretKey
            ])->post("{$this->flaskUrl}/api/laravel/generate-roadmap", $validated);

            return $response->json();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Roadmap generation failed'], 500);
        }
    }

    /**
     * 3. SKILL EXPANDER
     * Explains a skill and suggests projects.
     */
    public function expandSkill(Request $request)
    {
        $validated = $request->validate([
            'skill' => 'required|string',
            'level' => 'nullable|string'
        ]);

        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $this->secretKey
            ])->post("{$this->flaskUrl}/api/ollama/skill-expand", $validated);

            return $response->json();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Skill expansion failed'], 500);
        }
    }

    /**
     * 4. QUIZ GENERATOR
     * Handles either a File Upload OR a Text Topic.
     */
    public function generateQuiz(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:pdf,txt|max:2048',
            'query' => 'nullable|string'
        ]);

        try {
            $url = "{$this->flaskUrl}/api/ollama/generate-quiz";
            $headers = ['X-Internal-Secret' => $this->secretKey];

            // Case A: File Upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $response = Http::withHeaders($headers)
                    ->attach('file', file_get_contents($file), $file->getClientOriginalName())
                    ->post($url);
            }
            // Case B: Text Query / Course Code
            else {
                $response = Http::withHeaders($headers)->post($url, [
                    'query' => $request->input('query')
                ]);
            }

            return $response->json();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Quiz generation failed'], 500);
        }
    }

    /**
     * 5. WEBSITE CHATBOT
     * Proxies chat queries to the website context bot.
     */
    public function chatWithBot(Request $request)
    {
        $request->validate(['query' => 'required|string']);

        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $this->secretKey
            ])->post("{$this->flaskUrl}/api/ollama/website-chat", [
                'query' => $request->input('query')
            ]);

            return $response->json();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Chatbot unavailable'], 500);
        }
    }
    /**
     * 6. CAREER SKILL SUGGESTIONS
     * Asks AI for top skills required for a specific career role.
     */
    public function suggestSkills(Request $request)
    {
        $validated = $request->validate([
            'career' => 'required|string',
            'level'  => 'nullable|string' // e.g. 'Junior', 'Senior'
        ]);

        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $this->secretKey
            ])->post("{$this->flaskUrl}/api/ollama/suggest-skills-by-career", [
                'career' => $validated['career'],
                'level'  => $validated['level'] ?? 'Entry-Level'
            ]);

            return $response->json();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Skill suggestion failed'], 500);
        }
    }
    /**
     * 7. QUIZ GRADER
     * Compares user answers with the original quiz and returns a score.
     */
    public function gradeQuiz(Request $request)
    {
        $validated = $request->validate([
            'user_answers' => 'required|array',
            'original_quiz' => 'required|array'
        ]);

        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $this->secretKey
            ])->post("{$this->flaskUrl}/api/ollama/grade-quiz", $validated);

            return $response->json();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Grading failed'], 500);
        }
    }
}
