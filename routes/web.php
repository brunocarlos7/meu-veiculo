<?php

use Illuminate\Support\Facades\Route;

// Catch-all: serve the Vue SPA for ANY non-API route
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
