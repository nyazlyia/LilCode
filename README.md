# Petualangan Kode

Web edukasi interaktif untuk anak SD kelas 3-6. Anak menyusun blok perintah untuk membantu Kiko si Kancil
mencapai bendera, dan belajar **urutan**, **pengulangan**, dan **kondisi**. Tanpa daftar, tanpa login.
Kalau buntu, Pipo si Burung Hantu (Teman AI) memberi petunjuk, bukan kunci jawaban.

Dibuat untuk M-One Telkomsel Coding Competition, Kategori Umum.
Tema: *Innovating Education Through Technology*, subtema *Web Education for Kids*.

- Demo: https://lilcode.vercel.app

## Fitur

- 15 level di 3 dunia: Hutan Urutan, Sungai Pengulangan, Gua Kondisi
- Blok: Maju, Belok Kiri/Kanan, Ulangi N Kali, Jika Ada Rintangan (dengan bagian "Maka" dan "Kalau tidak")
- Animasi Kiko per langkah, sorotan blok yang sedang berjalan, dan konfeti saat menang
- Bintang 1-3, poin, dan 8 lencana, disimpan di `localStorage` (tanpa akun)
- Teman AI Pipo (Google Gemini) dengan petunjuk bertahap, batas 5 per level, dan petunjuk cadangan
- Halaman Untuk Guru dan Orang Tua, peta dunia, dan koleksi lencana
- Responsif (HP sampai laptop), mendukung `prefers-reduced-motion`, SVG buatan sendiri

## Teknologi

Laravel (polos, tanpa starter kit) - Blade - Tailwind CSS - Alpine.js (Vite) - TiDB Cloud (opsional) - Google Gemini API - Vercel.

## Menjalankan di lokal

Prasyarat: PHP 8.3+, Composer, Node.js 20+.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000. Untuk pengembangan, jalankan `npm run dev` di terminal lain.

### Variabel lingkungan

| Variabel | Fungsi |
|---|---|
| `APP_KEY` | Kunci aplikasi (`php artisan key:generate`) |
| `SESSION_DRIVER=cookie`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync` | Tanpa tabel sesi/cache (cocok untuk serverless) |
| `GEMINI_API_KEY`, `GEMINI_MODEL` | Teman AI. Tanpa kunci, Pipo memakai petunjuk cadangan |
| `LEVELS_SOURCE` | `json` (default) atau `database` |
| `DB_*`, `MYSQL_ATTR_SSL_CA` | Hanya jika `LEVELS_SOURCE=database` (TiDB Cloud) |

Kunci dan kata sandi **tidak pernah** dimasukkan ke repository. Isi di `.env` (lokal) dan Environment Variables Vercel.

### Memakai TiDB (opsional)

```bash
php artisan migrate
php artisan db:seed --class=LevelSeeder
# lalu set LEVELS_SOURCE=database
```

Jika database tidak bisa dibaca, aplikasi otomatis memakai `database/data/levels.json`.

## Pengujian

```bash
npm run test:js        # engine, komponen, penyimpanan, lencana
php artisan test       # mentor AI, katalog level, halaman
```

Tes `LevelCatalogTest` hanya berjalan dengan `DB_CONNECTION=sqlite` di `phpunit.xml` (melindungi database sungguhan dari `migrate:fresh`).

## Deploy ke Vercel

- Preset **Other**; `vercel.json` memakai runtime `vercel-php`.
- Hasil `npm run build` (`public/build`) di-commit (`git add -f public/build`) karena Vercel tidak menjalankan build Node untuk proyek PHP.
- Isi Environment Variables (`APP_KEY`, `GEMINI_API_KEY`, ...) di dasbor Vercel, lalu deploy ulang.
