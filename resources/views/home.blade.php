@extends('layouts.app')

@section('content')
<section class="grid items-center gap-10 py-6 lg:grid-cols-2 lg:py-12">
    <div>
        <span class="tag">Gratis · Tanpa daftar · Kelas 3-6 SD</span>
        <h1 class="mt-5 text-5xl font-extrabold sm:text-6xl">
            Ayo jadi <span class="text-ruby">petualang kode</span>!
        </h1>
        <p class="mt-5 max-w-xl text-xl">
            Bantu Kiko si Kancil melewati hutan dan sungai. Susun blok perintah, tekan jalankan,
            dan lihat Kiko berjalan sesuai rencanamu. Kalau buntu, Pipo si Burung Hantu siap kasih petunjuk.
        </p>
        <div class="mt-8 flex flex-wrap gap-4" x-data="resume(@js($levels))">
            <a :href="nextUrl ?? '{{ route('map') }}'" class="btn btn-run">
                <svg class="ico" aria-hidden="true"><use href="#i-play"/></svg>
                <span x-text="hasProgress ? 'Lanjutkan Petualangan' : 'Mulai Petualangan'">Mulai Petualangan</span>
            </a>
            <a href="{{ route('map') }}" class="btn btn-sun">
                <svg class="ico" aria-hidden="true"><use href="#i-map"/></svg> Peta Dunia
            </a>
        </div>

        <ul class="mt-10 grid gap-4 sm:grid-cols-3">
            <li class="card p-4">
                <span class="grid h-11 w-11 place-items-center rounded-full border-[3px] border-ink bg-sun"><svg class="ico" aria-hidden="true"><use href="#i-forward"/></svg></span>
                <p class="mt-2 font-display text-xl font-extrabold">Urutan</p>
                <p class="text-base leading-snug">Langkah demi langkah, harus tepat.</p>
            </li>
            <li class="card p-4">
                <span class="grid h-11 w-11 place-items-center rounded-full border-[3px] border-ink bg-blush"><svg class="ico" aria-hidden="true"><use href="#i-reset"/></svg></span>
                <p class="mt-2 font-display text-xl font-extrabold">Ulangi</p>
                <p class="text-base leading-snug">Satu blok, banyak gerakan.</p>
            </li>
            <li class="card p-4">
                <span class="grid h-11 w-11 place-items-center rounded-full border-[3px] border-ink bg-ruby text-paper"><svg class="ico" aria-hidden="true"><use href="#i-fork"/></svg></span>
                <p class="mt-2 font-display text-xl font-extrabold">Jika...</p>
                <p class="text-base leading-snug">Pilih jalan sesuai keadaan.</p>
            </li>
        </ul>
    </div>

    {{-- Ilustrasi pemandangan --}}
    <div class="relative mx-auto w-full max-w-md">
        <div class="stage" style="width:100%">
            <svg viewBox="0 0 240 240" class="absolute inset-0 h-full w-full" aria-hidden="true">
                <rect width="240" height="240" fill="#F8F6E8"/>
                <rect y="80" width="240" height="80" fill="#E3AFC9"/>
                <g>
                    @for ($c = 0; $c < 4; $c++)
                        <use href="#t2" x="{{ $c * 60 }}" y="80" width="60" height="80"/>
                    @endfor
                </g>
                <use href="#t3" x="60" y="80" width="60" height="80"/>
                <use href="#t3" x="120" y="80" width="60" height="80"/>
                <use href="#t1" x="150" y="10" width="60" height="60"/>
                <use href="#t0" x="20" y="170" width="60" height="60"/>
                <use href="#t0" x="110" y="165" width="60" height="60"/>
                <use href="#flag" x="170" y="165" width="64" height="64"/>
                <use href="#kiko" x="6" y="22" width="78" height="78"/>
            </svg>
        </div>
        <svg class="float absolute -right-3 -top-8 h-24 w-24 sm:-right-8" viewBox="0 0 64 64" aria-hidden="true"><use href="#pipo"/></svg>
    </div>
</section>
@endsection
