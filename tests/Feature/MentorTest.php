<?php

namespace Tests\Feature;

use App\Services\LevelCatalog;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MentorTest extends TestCase
{
    private function payload(array $override = []): array
    {
        return array_merge([
            'level' => '1-1',
            'program' => [['type' => 'forward']],
            'status' => 'incomplete',
            'tier' => 1,
        ], $override);
    }

    private function fakeGemini(string $text, int $status = 200): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(
                $status === 200 ? ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]] : ['error' => 'x'],
                $status,
            ),
        ]);
    }

    public function test_memakai_hint_gemini_jika_berhasil(): void
    {
        config(['services.gemini.key' => 'kunci-uji']);
        $this->fakeGemini('Wah, usahamu bagus! Coba hitung lagi petak menuju jembatan.');

        $this->postJson('/api/mentor', $this->payload())
            ->assertOk()
            ->assertJson(['source' => 'ai', 'hint' => 'Wah, usahamu bagus! Coba hitung lagi petak menuju jembatan.']);
    }

    public function test_kunci_api_dikirim_lewat_header_bukan_url(): void
    {
        config(['services.gemini.key' => 'kunci-uji']);
        $this->fakeGemini('Ayo coba lagi, kamu hampir berhasil!');

        $this->postJson('/api/mentor', $this->payload())->assertOk();

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'kunci-uji')
            && ! str_contains($request->url(), 'kunci-uji'));
    }

    public function test_fallback_jika_gemini_error(): void
    {
        config(['services.gemini.key' => 'kunci-uji']);
        $this->fakeGemini('', 500);

        $this->postJson('/api/mentor', $this->payload())
            ->assertOk()
            ->assertJson(['source' => 'fallback'])
            ->assertJsonStructure(['hint', 'source']);
    }

    public function test_fallback_jika_kunci_api_kosong(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->postJson('/api/mentor', $this->payload(['tier' => 2]))
            ->assertOk()
            ->assertJson(['source' => 'fallback']);

        Http::assertNothingSent();
    }

    public function test_fallback_jika_jawaban_terlihat_seperti_kunci_jawaban(): void
    {
        config(['services.gemini.key' => 'kunci-uji']);
        $this->fakeGemini('Jawabannya: pakai Ulangi 3 kali lalu Maju, Belok Kiri, Maju, Belok Kanan.');

        $this->postJson('/api/mentor', $this->payload())
            ->assertOk()
            ->assertJson(['source' => 'fallback']);
    }

    public function test_input_tidak_valid_ditolak(): void
    {
        $this->postJson('/api/mentor', $this->payload(['level' => '9-9']))->assertStatus(422);
        $this->postJson('/api/mentor', $this->payload(['tier' => 7]))->assertStatus(422);
        $this->postJson('/api/mentor', $this->payload(['status' => 'hack']))->assertStatus(422);
        $this->postJson('/api/mentor', ['level' => '1-1', 'tier' => 1])->assertStatus(422);
    }

    public function test_data_level_untuk_browser_tidak_memuat_rahasia(): void
    {
        $level = app(LevelCatalog::class)->find('1-1');
        $html = $this->get('/level/1-1')->assertOk()->getContent();

        // Kunci rahasia (di dalam JSON level, tanda kutip ditulis sebagai \u0022).
        $this->assertStringNotContainsString('\u0022solution\u0022', $html);
        $this->assertStringNotContainsString('\u0022solution_outline\u0022', $html);
        $this->assertStringNotContainsString('\u0022hints\u0022', $html);

        // Isi rahasianya sendiri: catatan solusi dan semua petunjuk cadangan.
        $this->assertStringNotContainsString($level['solution_outline'], $html);
        foreach ($level['hints'] as $hint) {
            $this->assertStringNotContainsString($hint['body'], $html);
        }
    }
}
