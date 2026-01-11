<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the user's projects.
     */
    public function index()
    {
        // Fetch projects belonging to the logged-in student
        $projects = Auth::user()->projects()->latest()->get();

        return response()->json([
            'data' => $projects
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'link'     => 'nullable|url|max:255',
            'about'    => 'required|string',
            'category' => 'required|in:Personal,Paid',
            'value'    => 'nullable|numeric|min:0',
            'skills'   => 'nullable|array',
            'tools'    => 'nullable|array',
        ]);

        // Create project associated with the user
        $project = Auth::user()->projects()->create($validated);

        return response()->json([
            'message' => 'Project initialized successfully',
            'data'    => $project
        ], 201);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        // Ensure user owns the project
        if ($project->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access to this artifact.'], 403);
        }

        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'link'     => 'nullable|url|max:255',
            'about'    => 'required|string',
            'category' => 'required|in:Personal,Paid',
            'value'    => 'nullable|numeric|min:0',
            'skills'   => 'nullable|array',
            'tools'    => 'nullable|array',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Project protocol updated',
            'data'    => $project
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project purged from database'
        ]);
    }
}
