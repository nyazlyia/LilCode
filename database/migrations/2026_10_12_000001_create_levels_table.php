<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 20)->unique();
            $table->unsignedTinyInteger('world');
            $table->unsignedTinyInteger('position');
            $table->string('title', 100);
            $table->string('story', 300);
            $table->string('concept', 20); // sequencing | loops | conditionals | mixed
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->unsignedTinyInteger('grid_width');
            $table->unsignedTinyInteger('grid_height');
            $table->json('grid');
            $table->json('start');
            $table->json('goal');
            $table->json('allowed_blocks');
            $table->unsignedTinyInteger('max_blocks');
            $table->unsignedTinyInteger('optimal_blocks');
            $table->text('solution_outline')->nullable(); // RAHASIA: hanya untuk konteks Gemini
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['world', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
