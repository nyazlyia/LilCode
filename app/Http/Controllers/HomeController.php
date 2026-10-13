<?php

namespace App\Http\Controllers;

use App\Services\LevelCatalog;

class HomeController extends Controller
{
    public function index(LevelCatalog $catalog)
    {
        return view('home', ['levels' => $catalog->summary()]);
    }
}
