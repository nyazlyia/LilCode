@extends('layouts.app')

@section('title', 'Koleksi Lencana | Petualangan Kode')
@section('description', 'Kumpulkan lencana dengan menyelesaikan level di Petualangan Kode.')

@section('content')
<div x-data="badgeShelf(@js($worlds))" class="grid gap-8 pt-2">
    <section class="grid items-center gap-5 sm:grid-cols-[1fr_auto]">
        <div>
            <h1 class="text-4xl font-extrabold sm:text-5xl">Koleksi Lencana</h1>
            <p class="mt-2 text-xl">Selesaikan tantangan untuk membuka lencana baru.</p>
        </div>
        <div class="card flex items-center gap-5 px-5 py-3">
            <p class="font-display text-2xl font-extrabold"><span x-text="earned"></span>/<span x-text="badges.length"></span> <span class="text-base font-semibold">lencana</span></p>
            <p class="font-display text-2xl font-extrabold"><span x-text="points"></span> <span class="text-base font-semibold">poin</span></p>
        </div>
    </section>

    <ul class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
        <template x-for="b in badges" :key="b.id">
            <li class="card grid justify-items-center gap-2 p-4 text-center">
                <span class="medal" :class="{ 'medal-off': !b.earned }">
                    <svg class="ico" aria-hidden="true"><use :href="'#i-' + b.icon"/></svg>
                </span>
                <p class="font-display text-lg font-extrabold leading-tight" x-text="b.name"></p>
                <p class="text-sm leading-snug" x-text="b.desc"></p>
                <p class="tag" x-show="b.earned">Didapat</p>
                <p class="text-sm font-semibold opacity-60" x-show="!b.earned">Belum didapat</p>
            </li>
        </template>
    </ul>

    <p><a href="{{ route('map') }}" class="btn btn-sun"><svg class="ico" aria-hidden="true"><use href="#i-map"/></svg> Kembali ke Peta</a></p>
</div>
@endsection
