<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // POST /register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Auto-hashed by model cast
        ]);

        $user->assignRole('student');

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    // POST /login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json(['message' => 'Login successful']);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // POST /logout
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    // GET /user
    public function user(Request $request)
    {
        return $request->user();
    }

    // GET /create-api-token
    public function createApiToken(Request $request)
    {
        $token = $request->user()->createToken('flask-token');

        return response()->json([
            'token' => $token->plainTextToken,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // [FIX] Use 'sometimes' so fields are not mandatory if not sent
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'bio'           => 'sometimes|nullable|string|max:500',
            'github'        => 'sometimes|nullable|url',
            'linkedin'      => 'sometimes|nullable|url',
            'career_id'     => 'sometimes|nullable|exists:careers,id',
            'avatar'        => 'sometimes|nullable|url',
        ]);

        // Update other fields if present in the request
        // This dynamically fills only the fields sent in $request
        $user->fill($request->only(['name', 'bio', 'github', 'linkedin', 'career_id', 'avatar']));

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load('career'),
        ]);
    }

    /**
     * PUT /api/user/password
     * Updates the authenticated user's password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

}
