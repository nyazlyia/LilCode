<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hints', function (Blueprint $table) {
            $table->id();
            // Tanpa foreign key (kompatibilitas TiDB Serverless); integritas dijaga seeder.
            $table->unsignedBigInteger('level_id')->index();
            $table->unsignedTinyInteger('tier'); // 1 pemantik, 2 konsep, 3 tunjuk bagian
            $table->string('error_type', 20)->nullable(); // null = berlaku umum
            $table->string('body', 300);
            $table->timestamps();

            $table->index(['level_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hints');
    }
};
