<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/peta', [LevelController::class, 'map'])->name('map');
Route::get('/lencana', [LevelController::class, 'badges'])->name('badges');
Route::view('/panduan', 'guide')->name('guide');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/level/{slug}', [LevelController::class, 'show'])
    ->where('slug', '[1-3]-[1-5]')
    ->name('level');

Route::post('/mentor', [MentorController::class, 'hint'])
    ->middleware('throttle:10,1')
    ->name('mentor');
Route::post('/api/mentor', [MentorController::class, 'hint'])
    ->middleware('throttle:10,1');