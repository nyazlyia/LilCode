<?php

namespace App\Http\Controllers;

use App\Http\Requests\MentorRequest;
use App\Services\GeminiMentorService;
use App\Services\LevelCatalog;
use Illuminate\Http\JsonResponse;

class MentorController extends Controller
{
    public function hint(MentorRequest $request, LevelCatalog $catalog, GeminiMentorService $mentor): JsonResponse
    {
        $level = $catalog->find($request->string('level')->toString());
        abort_if($level === null, 404);

        return response()->json($mentor->hint(
            $level,
            $request->input('program', []),
            (int) $request->input('tier'),
            $request->input('status'),
            $request->input('question'),
        ));
    }
}
