<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    // GET /api/careers
    // Returns list of all available careers
    public function index()
    {
        return response()->json(Career::all());
    }

    // POST /api/user/career
    // Updates the logged-in user's career
    public function update(Request $request)
    {
        $request->validate([
            'career_id' => 'required|exists:careers,id',
        ]);

        $user = $request->user();
        $user->career_id = $request->input('career_id');
        $user->save();

        // Return user with the career details loaded
        return response()->json([
            'message' => 'Career path updated successfully',
            'user' => $user->load('career'),
        ]);
    }
}
