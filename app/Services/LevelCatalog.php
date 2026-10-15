<?php

namespace App\Services;

use App\Models\Level;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sumber data level. Membaca dari TiDB (tabel levels + hints) bila LEVELS_SOURCE=database,
 * dan selalu kembali ke database/data/levels.json jika database tidak bisa dibaca atau kosong,
 * sehingga permainan tidak pernah berhenti karena masalah koneksi.
 */
class LevelCatalog
{
    public const WORLDS = [
        1 => ['name' => 'Hutan Urutan', 'blurb' => 'Susun langkah satu per satu dengan tepat.', 'color' => 'sun'],
        2 => ['name' => 'Sungai Pengulangan', 'blurb' => 'Satu blok Ulangi bisa menggantikan banyak langkah.', 'color' => 'blush'],
        3 => ['name' => 'Gua Kondisi', 'blurb' => 'Pilih jalan sesuai keadaan di depan Kiko.', 'color' => 'ruby'],
    ];

    private ?Collection $memo = null;

    /** @return Collection<int, array<string, mixed>> */
    public function all(): Collection
    {
        return $this->memo ??= $this->load();
    }

    private function load(): Collection
    {
        if (config('petualangan.levels_source') === 'database') {
            try {
                $levels = $this->fromDatabase();
                if ($levels->isNotEmpty()) {
                    return $levels;
                }
                Log::warning('Tabel levels kosong, memakai levels.json');
            } catch (Throwable $e) {
                Log::warning('Gagal membaca level dari database, memakai levels.json', ['error' => $e->getMessage()]);
            }
        }

        return $this->fromJson();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function fromDatabase(): Collection
    {
        return Level::query()
            ->with('hints')
            ->where('is_published', true)
            ->orderBy('world')
            ->orderBy('position')
            ->get()
            ->map(fn (Level $level) => $level->toCatalogArray())
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function fromJson(): Collection
    {
        $json = file_get_contents(database_path('data/levels.json'));

        return collect(json_decode($json, true, 512, JSON_THROW_ON_ERROR))
            ->sortBy([['world', 'asc'], ['position', 'asc']])
            ->values();
    }

    /** @return array<string, mixed>|null */
    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /** Slug level berikutnya menurut urutan, atau null jika terakhir. */
    public function nextSlug(string $slug): ?string
    {
        $all = $this->all();
        $index = $all->search(fn (array $level) => $level['slug'] === $slug);

        return $index === false ? null : ($all->get($index + 1)['slug'] ?? null);
    }

    /** Buang data rahasia sebelum dikirim ke browser. */
    public function exposed(array $level): array
    {
        return Arr::except($level, ['solution', 'solution_outline', 'hints']);
    }

    /** Level di dunia yang sama (untuk navigasi kecil). */
    public function siblings(array $level): array
    {
        return $this->all()
            ->where('world', $level['world'])
            ->map(fn (array $l) => ['slug' => $l['slug'], 'url' => route('level', $l['slug'])])
            ->values()
            ->all();
    }

    /** Dunia beserta level-nya untuk halaman peta (dunia tanpa level tampil sebagai "segera hadir"). */
    public function worlds(): array
    {
        $levels = $this->all()->groupBy('world');

        return collect(self::WORLDS)->map(function (array $meta, int $world) use ($levels) {
            return $meta + [
                'world' => $world,
                'levels' => $levels->get($world, collect())
                    ->map(fn (array $l) => [
                        'slug' => $l['slug'],
                        'title' => $l['title'],
                        'position' => $l['position'],
                        'url' => route('level', $l['slug']),
                    ])
                    ->values()
                    ->all(),
            ];
        })->values()->all();
    }

    /** Daftar datar slug dan url, urut. */
    public function summary(): array
    {
        return $this->all()
            ->map(fn (array $l) => ['slug' => $l['slug'], 'url' => route('level', $l['slug'])])
            ->values()
            ->all();
    }
}
