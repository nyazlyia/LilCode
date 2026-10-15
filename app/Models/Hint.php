<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hint extends Model
{
    protected $guarded = [];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
