<?php

namespace App\Http\Controllers;

use App\Models\RoadmapTask; // 1. YOU MUST IMPORT THE MODEL
use Illuminate\Http\Request;

class RoadmapTaskController extends Controller
{
    /**
     * 1. CREATE NEW TASK
     * Route: POST /api/phases/{phaseId}/tasks
     */
public function store(Request $request, $phaseId)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'subtitle' => 'nullable|string|max:255',
    ]);

    // 1. Find the highest current order_index for this specific phase
    // If no tasks exist, max() returns null, so we default to 0.
    $maxOrder = \App\Models\RoadmapTask::where('phase_id', $phaseId)->max('order_index');

    // 2. Increment by 1
    $newOrder = $maxOrder ? $maxOrder + 1 : 1;

    // 3. Create the task with the calculated order
    $task = \App\Models\RoadmapTask::create([
        'phase_id' => $phaseId,
        'title'    => $request->title,
        'subtitle' => $request->subtitle,
        'completed' => false,
        'order_index' => $newOrder // <--- Uses the calculated value
    ]);

    return response()->json($task, 201);
}

    /**
     * 2. EDIT TASK DETAILS (Title/Subtitle)
     * Triggered by PUT /tasks/{id}
     */
    public function update(Request $request, $id)
    {
        $task = RoadmapTask::findOrFail($id);

        // Update only title and subtitle
        $task->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle
        ]);

        return response()->json($task);
    }

    /**
     * 3. TOGGLE STATUS (Your existing function)
     * Triggered by PATCH /tasks/{id}
     */
public function updateStatus(Request $request, $id) {
    $task = RoadmapTask::with('phase.roadmap')->findOrFail($id);

    // 1. Update Task Completion
    $task->update([
        'completed' => $request->completed
    ]);

    // 2. Recalculate Progress for the Roadmap
    $roadmap = $task->phase->roadmap;

    // Fetch all tasks related to this roadmap across all phases
    $allTasks = \App\Models\RoadmapTask::whereHas('phase', function($query) use ($roadmap) {
        $query->where('roadmap_id', $roadmap->id);
    })->get();

    $total = $allTasks->count();
    $completed = $allTasks->where('completed', true)->count();

    // 3. Calculate integer percentage
    $progressPercentage = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

    // 4. Update the Roadmap Progress Column
    $roadmap->update(['progress' => $progressPercentage]);

    return response()->json([
        'message' => 'Status updated',
        'progress' => $progressPercentage // Returns integer like 45
    ]);
}
}
