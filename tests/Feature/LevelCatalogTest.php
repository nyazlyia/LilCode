<?php

namespace Tests\Feature;

use App\Models\Hint;
use App\Models\Level;
use App\Services\LevelCatalog;
use Database\Seeders\LevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LevelCatalogTest extends TestCase
{
    use RefreshDatabase;

    /** RefreshDatabase menjalankan migrate:fresh: JANGAN sampai mengenai TiDB sungguhan. */
    protected function setUp(): void
    {
        $connection = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION');
        if ($connection !== 'sqlite') {
            $this->markTestSkipped('Tes ini menjalankan migrate:fresh; hanya jalan jika phpunit.xml memakai DB_CONNECTION=sqlite.');
        }

        parent::setUp();
    }

    public function test_seeder_memuat_semua_level_dan_petunjuk(): void
    {
        $this->seed(LevelSeeder::class);

        $this->assertSame(15, Level::count());
        $this->assertSame(45, Hint::count());
    }

    public function test_seeder_aman_dijalankan_berulang(): void
    {
        $this->seed(LevelSeeder::class);
        $this->seed(LevelSeeder::class);

        $this->assertSame(15, Level::count());
        $this->assertSame(45, Hint::count());
    }

    public function test_solusi_lengkap_tidak_disimpan_di_database(): void
    {
        $this->seed(LevelSeeder::class);

        $this->assertArrayNotHasKey('solution', Level::first()->getAttributes());
        $this->assertArrayNotHasKey('solution_outline', Level::first()->toArray()); // $hidden
    }

    public function test_mode_database_sama_dengan_mode_json(): void
    {
        $this->seed(LevelSeeder::class);

        config(['petualangan.levels_source' => 'json']);
        $json = (new LevelCatalog())->all()
            ->map(fn (array $level) => Arr::except($level, ['solution']))
            ->all();

        config(['petualangan.levels_source' => 'database']);
        $db = (new LevelCatalog())->all()->all();

        $this->assertCount(15, $db);
        $this->assertEquals($json, $db);
    }

    public function test_kembali_ke_json_jika_tabel_kosong_atau_hilang(): void
    {
        config(['petualangan.levels_source' => 'database']);

        // tabel ada tetapi kosong
        $this->assertCount(15, (new LevelCatalog())->all());

        // tabel hilang (simulasi database bermasalah)
        Schema::drop('hints');
        Schema::drop('levels');
        $this->assertCount(15, (new LevelCatalog())->all());
    }
}
