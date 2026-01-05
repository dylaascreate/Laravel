<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    /**
     * Get all skills for selection in the UI.
     */
    public function index()
    {
        return response()->json(Skill::all());
    }

    /**
     * Sync user's current skills to the database.
     * Used before generating a new roadmap.
     */
    public function syncUserSkills(Request $request)
    {
        $request->validate([
            'skills' => 'required|array',
            'skills.*.id' => 'required|exists:skills,id',
            'skills.*.proficiency' => 'required|integer|min:0|max:100',
        ]);

        $user = Auth::user();

        // Prepare data for the sync method
        $syncData = [];
        foreach ($request->skills as $skill) {
            $syncData[$skill['id']] = ['proficiency' => $skill['proficiency']];
        }

        $user->skills()->sync($syncData);

        return response()->json(['message' => 'Skills updated successfully']);
    }
}
