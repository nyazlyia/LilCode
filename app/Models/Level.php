<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $guarded = [];

    /** Jangan pernah ikut ter-serialisasi ke respons mana pun. */
    protected $hidden = ['solution_outline'];

    protected function casts(): array
    {
        return [
            'grid' => 'array',
            'start' => 'array',
            'goal' => 'array',
            'allowed_blocks' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class)->orderBy('tier');
    }

    /**
     * Bentuk array yang sama dengan entri di levels.json (tanpa kunci "solution"),
     * sehingga LevelCatalog mengembalikan data seragam dari JSON maupun database.
     *
     * @return array<string, mixed>
     */
    public function toCatalogArray(): array
    {
        return [
            'slug' => $this->slug,
            'world' => (int) $this->world,
            'position' => (int) $this->position,
            'title' => $this->title,
            'story' => $this->story,
            'concept' => $this->concept,
            'difficulty' => (int) $this->difficulty,
            'grid_width' => (int) $this->grid_width,
            'grid_height' => (int) $this->grid_height,
            'grid' => $this->grid,
            'start' => $this->start,
            'goal' => $this->goal,
            'allowed_blocks' => $this->allowed_blocks,
            'max_blocks' => (int) $this->max_blocks,
            'optimal_blocks' => (int) $this->optimal_blocks,
            'solution_outline' => $this->solution_outline,
            'hints' => $this->hints
                ->map(fn (Hint $hint) => array_filter([
                    'tier' => (int) $hint->tier,
                    'error_type' => $hint->error_type,
                    'body' => $hint->body,
                ], fn ($value) => $value !== null))
                ->values()
                ->all(),
        ];
    }
}
