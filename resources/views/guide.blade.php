@extends('layouts.app')

@section('title', 'Untuk Guru dan Orang Tua | Petualangan Kode')
@section('description', 'Panduan singkat bagi guru dan orang tua: tujuan belajar, cara mendampingi anak, peran Teman AI Pipo, dan privasi di Petualangan Kode.')

@section('content')
<article class="mx-auto grid max-w-3xl gap-8 pt-2">
    <header>
        <span class="tag">Untuk Guru dan Orang Tua</span>
        <h1 class="mt-4 text-4xl font-extrabold sm:text-5xl">Belajar berpikir seperti programmer, tanpa mengetik kode</h1>
        <p class="mt-3 text-xl">
            Petualangan Kode melatih anak SD kelas 3 sampai 6 menyusun langkah, mengulang pola, dan memilih tindakan sesuai keadaan.
            Anak bermain lewat teka-teki blok perintah bersama Kiko si Kancil. Tidak perlu daftar atau membuat akun.
        </p>
    </header>

    <section class="card grid gap-3 p-5" aria-labelledby="tujuan">
        <h2 id="tujuan" class="text-2xl font-extrabold">Apa yang dilatih</h2>
        <ul class="grid gap-3">
            <li><strong>Dunia 1, Hutan Urutan.</strong> Menyusun langkah dengan urutan yang tepat. Anak belajar bahwa komputer mengikuti perintah persis seperti yang ditulis.</li>
            <li><strong>Dunia 2, Sungai Pengulangan.</strong> Mengenali pola dan merangkumnya dengan blok Ulangi, sehingga satu perintah menggantikan banyak langkah.</li>
            <li><strong>Dunia 3, Gua Kondisi.</strong> Memeriksa keadaan sebelum bertindak dengan blok Jika Ada Rintangan: kalau terhalang lakukan ini, kalau tidak lakukan itu.</li>
        </ul>
        <p>Semua ini bagian dari <em>berpikir komputasional</em>: memecah masalah, mengenali pola, dan menyusun langkah penyelesaian.</p>
    </section>

    <section class="card grid gap-3 p-5" aria-labelledby="cara">
        <h2 id="cara" class="text-2xl font-extrabold">Cara memakai</h2>
        <ol class="grid list-decimal gap-2 pl-6">
            <li>Buka halaman <a href="{{ route('map') }}" class="font-bold underline">Peta</a> dan pilih level yang terbuka.</li>
            <li>Baca cerita singkat, lalu ketuk blok untuk menyusunnya di area <em>Susunanmu</em>.</li>
            <li>Tekan <strong>Jalankan</strong> dan amati Kiko berjalan. Setiap blok yang sedang dikerjakan akan menyala.</li>
            <li>Kalau Kiko salah jalan, ubah susunannya dan coba lagi. Mencoba berulang kali adalah bagian dari belajar.</li>
            <li>Menang berarti 1 sampai 3 bintang. Bintang lebih banyak untuk susunan yang hemat dan tanpa petunjuk.</li>
        </ol>
        <p>Bisa dipakai di HP, tablet, atau laptop sekolah. Cukup peramban dan sambungan internet.</p>
    </section>

    <section class="card grid gap-3 p-5" aria-labelledby="dampingi">
        <h2 id="dampingi" class="text-2xl font-extrabold">Tips mendampingi anak</h2>
        <ul class="grid list-disc gap-2 pl-6">
            <li>Tahan diri untuk tidak langsung memberi jawaban. Tanyakan, <em>"Menurutmu apa yang terjadi kalau Kiko menjalankan blok ini?"</em></li>
            <li>Minta anak menjelaskan susunannya dengan kata-katanya sendiri sebelum menekan Jalankan.</li>
            <li>Rayakan usaha, bukan hanya bintang. Gagal lalu memperbaiki adalah kemampuan yang sedang dilatih.</li>
            <li>Di kelas, ajak siswa berpasangan: satu menyusun, satu membaca langkah, lalu bertukar.</li>
        </ul>
    </section>

    <section class="card grid gap-3 p-5" aria-labelledby="pipo">
        <h2 id="pipo" class="flex items-center gap-3 text-2xl font-extrabold">
            <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true"><use href="#pipo"/></svg>
            Teman AI: Pipo
        </h2>
        <p>
            Pipo si burung hantu memberi <strong>petunjuk, bukan kunci jawaban</strong>. Petunjuknya bertahap:
            pertanyaan pemantik, lalu penjelasan konsep, lalu menunjuk bagian yang perlu dicermati.
            Setiap level dibatasi 5 petunjuk, dan memakai petunjuk dapat mengurangi bintang.
        </p>
        <p>
            Jawaban Pipo dibuat oleh model AI dan diperiksa agar tidak membocorkan jawaban lengkap.
            Kalau layanan AI sedang tidak tersedia, Pipo tetap memberi petunjuk yang sudah disiapkan untuk tiap level.
        </p>
    </section>

    <section class="card grid gap-3 p-5" aria-labelledby="privasi">
        <h2 id="privasi" class="text-2xl font-extrabold">Privasi</h2>
        <ul class="grid list-disc gap-2 pl-6">
            <li>Tanpa akun, tanpa nama asli, tanpa iklan, dan tanpa pelacak pihak ketiga.</li>
            <li>Bintang, poin, dan lencana disimpan <strong>hanya di peramban anak</strong> (penyimpanan lokal). Menghapus data peramban atau memakai tombol Hapus progres di Peta akan menghapusnya.</li>
            <li>Saat anak meminta petunjuk, yang dikirim ke server hanya susunan blok dan nomor level. Tidak ada data pribadi.</li>
        </ul>
    </section>

    <p class="flex flex-wrap gap-3">
        <a href="{{ route('level', '1-1') }}" class="btn btn-run"><svg class="ico" aria-hidden="true"><use href="#i-play"/></svg> Coba Level 1-1</a>
        <a href="{{ route('badges') }}" class="btn btn-sun"><svg class="ico" aria-hidden="true"><use href="#i-star"/></svg> Lihat Lencana</a>
    </p>
</article>
@endsection
