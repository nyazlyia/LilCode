<?php

namespace Database\Seeders;

use App\Models\Hint;
use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi tabel levels dan hints dari database/data/levels.json.
 * Aman dijalankan berulang (idempotent). Kunci "solution" sengaja tidak disimpan.
 */
class LevelSeeder extends Seeder
{
    private const FIELDS = [
        'world', 'position', 'title', 'story', 'concept', 'difficulty',
        'grid_width', 'grid_height', 'grid', 'start', 'goal', 'allowed_blocks',
        'max_blocks', 'optimal_blocks', 'solution_outline',
    ];

    public function run(): void
    {
        $levels = json_decode(
            file_get_contents(database_path('data/levels.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        DB::transaction(function () use ($levels) {
            foreach ($levels as $data) {
                $level = Level::updateOrCreate(
                    ['slug' => $data['slug']],
                    Arr::only($data, self::FIELDS) + ['is_published' => true],
                );

                Hint::where('level_id', $level->id)->delete();

                foreach ($data['hints'] ?? [] as $hint) {
                    Hint::create([
                        'level_id' => $level->id,
                        'tier' => $hint['tier'],
                        'error_type' => $hint['error_type'] ?? null,
                        'body' => $hint['body'],
                    ]);
                }
            }
        });
    }
}
