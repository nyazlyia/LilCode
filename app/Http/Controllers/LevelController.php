<?php

namespace App\Http\Controllers;

use App\Services\LevelCatalog;

class LevelController extends Controller
{
    public function __construct(private LevelCatalog $catalog)
    {
    }

    public function map()
    {
        return view('map', ['worlds' => $this->catalog->worlds()]);
    }

    public function show(string $slug)
    {
        $level = $this->catalog->find($slug);
        abort_if($level === null, 404);

        $next = $this->catalog->nextSlug($slug);

        return view('level', [
            'level' => $this->catalog->exposed($level),
            'nextUrl' => $next ? route('level', $next) : null,
            'siblings' => $this->catalog->siblings($level),
        ]);
    }
}
