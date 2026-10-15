<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Petualangan Kode - Belajar Coding Seru untuk Anak SD')</title>
    <meta name="description" content="@yield('description', 'Belajar urutan, pengulangan, dan kondisi lewat teka-teki blok perintah bersama Kiko si Kancil. Gratis, tanpa daftar, untuk anak SD kelas 3-6.')">
    <meta name="theme-color" content="#EFECD7">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="@yield('title', 'Petualangan Kode')">
    <meta property="og:description" content="Teka-teki blok perintah seru untuk anak SD kelas 3-6.">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sprite')

    <header class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-display text-2xl font-extrabold">
            <svg class="h-11 w-11" viewBox="0 0 64 64" aria-hidden="true"><use href="#pipo"/></svg>
            <span>Petualangan <span class="text-ruby">Kode</span></span>
        </a>
        <nav class="flex flex-wrap items-center gap-3" aria-label="Menu utama">
            <a href="{{ route('map') }}" class="btn">
                <svg class="ico" aria-hidden="true"><use href="#i-map"/></svg> Peta
            </a>
            <a href="{{ route('badges') }}" class="btn blk-forward">
                <svg class="ico" aria-hidden="true"><use href="#i-star"/></svg> Lencana
            </a>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-5 pb-16">
        @yield('content')
    </main>

    <footer class="mx-auto max-w-6xl px-5 pb-10 text-base">
        <p class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t-[3px] border-ink pt-5">
            <span class="font-display font-extrabold">Petualangan Kode</span>
            <a href="{{ route('guide') }}" class="underline">Untuk Guru dan Orang Tua</a>
            <a href="{{ route('map') }}" class="underline">Peta</a>
            <a href="{{ route('badges') }}" class="underline">Lencana</a>
        </p>
    </footer>
</body>
</html>
