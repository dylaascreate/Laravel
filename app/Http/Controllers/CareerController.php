<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CareerController extends Controller
{
    // =========================================================================
    // CRUD METHODS (For Admin Management)
    // =========================================================================

    // GET /api/careers
    public function index()
    {
        return response()->json(Career::all());
    }

    // POST /api/careers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|string'
        ]);

        $career = Career::create($validated);

        return response()->json(['data' => $career], 201);
    }

    // GET /api/careers/{id}
    public function show($id)
    {
        return response()->json(Career::findOrFail($id));
    }

    // PUT /api/careers/{id}
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|string'
        ]);

        $career = Career::findOrFail($id);
        $career->update($validated);

        return response()->json(['data' => $career]);
    }

    // DELETE /api/careers/{id}
    public function destroy($id)
    {
        $career = Career::findOrFail($id);
        $career->delete();

        return response()->json(['message' => 'Career deleted successfully']);
    }

    // =========================================================================
    // STUDENT/USER METHODS
    // =========================================================================

    // POST /api/career
    // Updates the logged-in user's career preference
    // RENAMED from 'update' to 'updateUserCareer' to avoid conflict with CRUD update
    public function updateUserCareer(Request $request)
    {
        $request->validate([
            'career_id' => 'required|exists:careers,id',
        ]);

        $user = $request->user();
        $user->career_id = $request->input('career_id');
        $user->save();

        return response()->json([
            'message' => 'Career path updated successfully',
            'user' => $user->load('career'),
        ]);
    }

    /**
     * POST /api/careers/recommend
     * Sends user skills to the Python AI service.
     */
    public function recommend(Request $request)
    {
        $request->validate([
            'skills' => 'required|array',
            'skills.*.name' => 'required|string',
        ]);

        $flaskUrl = 'http://127.0.0.1:5001/api/ollama/career-recommend';

        try {
            $response = Http::post($flaskUrl, [
                'skills' => $request->input('skills')
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => 'Failed to fetch recommendations.',
                    'details' => $response->body()
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Service unavailable.'], 500);
        }
    }
}
