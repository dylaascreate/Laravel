<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use Illuminate\Support\Facades\Http;

class SyncToFlask extends Command
{
    // The command you type in the terminal
    protected $signature = 'sync:to-flask';

    protected $description = 'Push all courses FROM Laravel TO Local Flask';

    public function handle()
    {
        // 1. Config
        $flaskUrl = 'http://127.0.0.1:5000/api/laravel/sync-courses';
        $secret   = env('FLASK_API_KEY', 'your_secret_key'); // Match this with Flask config

        $this->info("Fetching courses from Laravel Database...");

        // 2. Get Data & Format it for Flask
        // We use map() to match the exact keys your JSON expects
        $courses = Course::all()->map(function ($course) {
            return [
                'course_code'            => $course->course_code, // or $course->code depending on your DB column
                'course_name'            => $course->course_name, // or $course->title
                'description'            => $course->description,
                'next_course_code'       => $course->next_course_code,
                'category'               => $course->category,
                'credit'                 => $course->credit,
                // Ensure these are arrays. If stored as JSON string in DB, decode them.
                // If using $casts = ['associated_skills' => 'array'] in Model, this is automatic.
                'associated_skills'      => $course->associated_skills ?? [],
                'course_content_outline' => $course->course_content_outline ?? [],
            ];
        });

        $count = $courses->count();
        $this->info("Found {$count} courses. Pushing to Flask...");

        // 3. Send to Flask
        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $secret,
                'Content-Type' => 'application/json'
            ])->post($flaskUrl, $courses->toArray());

            if ($response->successful()) {
                $this->info("✅ Success! Flask response: " . $response->body());
            } else {
                $this->error("❌ Failed. Status: " . $response->status());
                $this->error("Response: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("Connection Error: Is Flask running at $flaskUrl?");
            $this->error($e->getMessage());
        }
    }
}
