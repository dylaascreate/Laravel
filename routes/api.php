<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'email'=> ['required', 'email'],
        'password'=> ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return response()->json(['message'=> 'Login successful']);
    }

    return response()->json(['message'=>'Invalid credentials'],401);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request){
    Auth::guard('web')->logout();
    $request->session()->Invalidate();
    $request->session()->regenerateToken();
    return response()->noContent();
});

Route::middleware('auth:sanctum')->
get('/user', function (Request $request) {
    return $request->user();
});
