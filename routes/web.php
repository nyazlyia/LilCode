<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MentorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/peta', [LevelController::class, 'map'])->name('map');

Route::get('/level/{slug}', [LevelController::class, 'show'])
    ->where('slug', '[1-3]-[1-5]')
    ->name('level');

Route::post('/mentor', [MentorController::class, 'hint'])
    ->middleware('throttle:10,1')
    ->name('mentor');