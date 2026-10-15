<?php

namespace Tests\Feature;

use Tests\TestCase;

class PagesTest extends TestCase
{
    public function test_halaman_utama_terbuka(): void
    {
        foreach (['/', '/peta', '/lencana', '/panduan', '/level/1-1', '/level/2-3', '/level/3-5'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_level_yang_tidak_ada_404(): void
    {
        $this->get('/level/9-9')->assertNotFound();
        $this->get('/level/1-9')->assertNotFound();
    }

    public function test_sitemap_memuat_semua_halaman_dan_level(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $xml = $response->getContent();
        $this->assertSame(4 + 15, substr_count($xml, '<url>'));
        $this->assertStringContainsString('/level/3-5', $xml);
        $this->assertNotFalse(simplexml_load_string($xml), 'sitemap harus XML valid');
    }

    public function test_header_keamanan_terpasang(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
