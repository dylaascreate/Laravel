<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{

    // =========================================================================
    // ADMIN / CATALOG METHODS (The Course Itself)
    // =========================================================================

    // [Admin/Public] View Catalog
    public function index()
    {
        return response()->json(['data' => Course::all()]);
    }

    // [Admin] Create New Course
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|unique:courses',
            'course_name' => 'required',
            // ... other fields
        ]);

        $course = Course::create($validated);
        return response()->json(['message' => 'Course created', 'data' => $course]);
    }

    // [Admin] Update Course Details
    public function update(Request $request, Course $course)
    {
        $course->update($request->all());
        return response()->json(['message' => 'Course updated', 'data' => $course]);
    }

    // [Admin] Delete Course
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }


    // =========================================================================
    // STUDENT METHODS (The Enrollment / Pivot)
    // =========================================================================

    // [Student] View My Enrolled Courses
    public function userCourses()
    {
        $user = Auth::user();

        $courses = $user->courses()
            ->withPivot('status', 'grade', 'id')
            // [FIX] Change 'pivot_updated_at' to 'course_user.updated_at'
            ->orderBy('course_user.updated_at', 'desc')
            ->get();

        return response()->json(['data' => $courses]);
    }

    // [Student] Enroll
    public function enroll(Request $request)
    {
        $request->validate(['course_id' => 'required|exists:courses,id']);
        $user = Auth::user();

        // Check duplicates
        if ($user->courses()->where('course_id', $request->course_id)->exists()) {
            return response()->json(['message' => 'Already enrolled'], 422);
        }

        $user->courses()->attach($request->course_id, ['status' => 'not_started']);
        return response()->json(['message' => 'Enrolled successfully']);
    }

    // [Student] Update Progress (Pivot Table)
    public function updateProgress(Request $request, $courseId)
    {
        $user = Auth::user();

        // Validation for Pivot fields
        $request->validate([
            'status' => 'in:not_started,in_progress,completed',
            'grade'  => 'nullable|string'
        ]);

        $user->courses()->updateExistingPivot($courseId, $request->only(['status', 'grade']));

        return response()->json(['message' => 'Progress updated']);
    }

    // [Student] Drop Course
    public function drop($courseId)
    {
        Auth::user()->courses()->detach($courseId);
        return response()->json(['message' => 'Course dropped']);
    }
}
