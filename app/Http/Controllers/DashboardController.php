<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'users_count' => 100,
            'sales_total' => 5000,
            'recent_activity' => ['Logged in', 'Updated profile'],
        ]);
    }
}
