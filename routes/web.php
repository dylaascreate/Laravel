<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{any}', function () {
    return view('welcome'); // Replace 'app' with your main Vue blade file (e.g., 'welcome', 'index')
})->where('any', '.*');
