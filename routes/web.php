<?php

use App\Http\Controllers\LevelController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/level/{slug}', [LevelController::class, 'show'])
    ->where('slug', '[1-3]-[1-5]')
    ->name('level');
