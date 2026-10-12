<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Sementara membaca level dari database/data/levels.json.
 * Nanti diganti ke model Level (TiDB) tanpa mengubah view.
 */
class LevelController extends Controller
{
    public function show(string $slug)
    {
        $levels = $this->levels();
        $index = $levels->search(fn (array $level) => $level['slug'] === $slug);

        abort_if($index === false, 404);

        $current = $levels->get($index);
        $next = $levels->get($index + 1)['slug'] ?? null;

        // Daftar level di dunia yang sama (untuk navigasi kecil di atas halaman).
        $siblings = $levels->where('world', $current['world'])
            ->map(fn (array $level) => ['slug' => $level['slug'], 'url' => route('level', $level['slug'])])
            ->values()
            ->all();

        return view('level', [
            'level' => $this->publicLevel($current),
            'nextUrl' => $next ? route('level', $next) : null,
            'siblings' => $siblings,
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function levels(): Collection
    {
        $json = file_get_contents(database_path('data/levels.json'));

        return collect(json_decode($json, true, 512, JSON_THROW_ON_ERROR))
            ->sortBy([['world', 'asc'], ['position', 'asc']])
            ->values();
    }

    /** Buang data rahasia sebelum dikirim ke browser. */
    private function publicLevel(array $level): array
    {
        return Arr::except($level, ['solution', 'solution_outline']);
    }
}
