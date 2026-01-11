<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    // ... (index, store, update, destroy, userMatrix methods remain identical) ...
    // COPY methods: index, store, update, destroy, userMatrix from your existing file
    // OR just replace the whole file with this block if you want to be safe:

    public function index(Request $request)
    {
        $query = Skill::query();
        if ($request->has('domain')) $query->where('domain', $request->domain);
        if ($request->has('search')) $query->where('name', 'like', '%' . $request->search . '%');
        return response()->json(['data' => $query->orderBy('name', 'asc')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
            'domain' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'in:Active,Draft,Deprecated'
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $skill = Skill::create($validated);
        return response()->json(['message' => 'Skill created successfully', 'data' => $skill], 201);
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('skills')->ignore($skill->id)],
            'domain' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
            'status' => 'in:Active,Draft,Deprecated'
        ]);
        if (isset($validated['name'])) $validated['slug'] = Str::slug($validated['name']);
        $skill->update($validated);
        return response()->json(['message' => 'Skill updated successfully', 'data' => $skill]);
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return response()->json(['message' => 'Skill deleted successfully']);
    }

    public function userMatrix(Request $request)
    {
        $user = Auth::user();
        $skills = $user->skills()
            ->select('skills.*', 'skill_user.proficiency', 'skill_user.verified')
            ->get()
            ->map(function ($skill) {
                $skill->proficiency = $skill->pivot->proficiency;
                $skill->verified = (bool) $skill->pivot->verified;
                unset($skill->pivot);
                return $skill;
            });
        return response()->json(['data' => $skills]);
    }

    // [!!!] THIS IS THE CRITICAL FIX [!!!]
    public function attachUserSkill(Request $request, $skillId)
    {
        $request->validate([
            'proficiency' => 'required|integer|min:0|max:100',
            'name'        => 'nullable|string',
            'verified'    => 'boolean', // 1. Allow this field
        ]);

        $user = Auth::user();
        $skill = null;

        if (is_numeric($skillId)) {
            $skill = Skill::find($skillId);
        }

        if (!$skill && $request->has('name')) {
            $skill = Skill::firstOrCreate(
                ['name' => $request->name],
                [
                    'slug'   => Str::slug($request->name),
                    'domain' => $request->domain ?? 'General',
                    'status' => 'Active'
                ]
            );
        }

        if (!$skill) {
            return response()->json(['message' => 'Invalid Skill ID or Name missing'], 422);
        }

        // 2. Prepare data to save
        $attributes = ['proficiency' => $request->proficiency];

        // 3. IF verified is sent, save it.
        if ($request->has('verified')) {
            $attributes['verified'] = $request->verified;
        }

        if ($user->skills()->where('skill_id', $skill->id)->exists()) {
            $user->skills()->updateExistingPivot($skill->id, $attributes);
        } else {
            // Default to false if not sent
            if (!isset($attributes['verified'])) $attributes['verified'] = false;
            $user->skills()->attach($skill->id, $attributes);
        }

        // 4. Return updated data so frontend updates correctly
        $updatedSkill = $user->skills()->where('skill_id', $skill->id)->first();
        $updatedSkill->proficiency = $updatedSkill->pivot->proficiency;
        $updatedSkill->verified = (bool) $updatedSkill->pivot->verified;
        unset($updatedSkill->pivot);

        return response()->json([
            'message' => 'Skill matrix updated',
            'data'    => $updatedSkill
        ]);
    }

    public function detachUserSkill($skillId)
    {
        $user = Auth::user();
        $user->skills()->detach($skillId);
        return response()->json(['message' => 'Skill removed from profile']);
    }
}
