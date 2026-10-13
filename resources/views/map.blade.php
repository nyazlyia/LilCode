@extends('layouts.app')

@section('title', 'Peta Petualangan | Petualangan Kode')
@section('description', 'Pilih level dan lihat bintangmu di Peta Petualangan. Tiga dunia: urutan, pengulangan, dan kondisi.')

@section('content')
<div x-data="worldMap(@js($worlds))" class="grid gap-8 pt-2">

    <section class="grid items-center gap-5 sm:grid-cols-[1fr_auto]">
        <div>
            <h1 class="text-4xl font-extrabold sm:text-5xl">Peta Petualangan</h1>
            <p class="mt-2 text-xl">Selesaikan level untuk membuka level berikutnya.</p>
        </div>
        <div class="card flex items-center gap-5 px-5 py-3">
            <p class="flex items-center gap-2 font-display text-2xl font-extrabold">
                <svg class="star on" style="--i:0;width:2.2rem;height:2.2rem" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-star"/></svg>
                <span><span x-text="stars"></span>/<span x-text="maxStars"></span></span>
            </p>
            <p class="font-display text-2xl font-extrabold"><span x-text="points"></span> <span class="text-base font-semibold">poin</span></p>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <a x-show="nextUrl" :href="nextUrl" class="btn btn-run">
            <svg class="ico" aria-hidden="true"><use href="#i-play"/></svg>
            <span x-text="hasProgress ? 'Lanjutkan' : 'Mulai Petualangan'"></span>
        </a>
        <p x-show="allDone" class="msg msg-ok">Semua level selesai. Hebat sekali!</p>
    </div>

    <template x-for="w in worlds" :key="w.world">
        <section class="card overflow-hidden" :aria-label="'Dunia ' + w.world">
            <header class="world-head" :class="'world-' + w.color">
                <h2 class="text-2xl font-extrabold sm:text-3xl">Dunia <span x-text="w.world"></span>: <span x-text="w.name"></span></h2>
                <p class="text-base" x-text="w.blurb"></p>
            </header>

            <p x-show="w.levels.length === 0" class="p-5 text-center font-display text-xl font-semibold opacity-70">Segera hadir</p>

            <ul class="grid grid-cols-3 gap-x-3 gap-y-6 p-5 sm:grid-cols-5">
                <template x-for="lv in w.levels" :key="lv.slug">
                    <li class="grid justify-items-center gap-1 text-center">
                        <a x-show="unlocked(lv.slug)" :href="lv.url" class="node" :class="done(lv.slug) ? 'node-done' : ''" :aria-label="'Level ' + lv.slug + ': ' + lv.title">
                            <span x-text="lv.position"></span>
                        </a>
                        <span x-show="!unlocked(lv.slug)" class="node node-locked" role="img" :aria-label="'Level ' + lv.slug + ' terkunci'">
                            <svg class="ico" aria-hidden="true"><use href="#i-lock"/></svg>
                        </span>
                        <span class="flex gap-0.5" aria-hidden="true">
                            <template x-for="n in 3" :key="n">
                                <svg class="star star-sm" :class="{ on: levelStars(lv.slug) >= n }" viewBox="0 0 24 24"><use href="#i-star"/></svg>
                            </template>
                        </span>
                        <span class="text-sm leading-tight" x-text="lv.title"></span>
                    </li>
                </template>
            </ul>
        </section>
    </template>

    <p class="text-center">
        <button type="button" class="link-btn" @click="resetProgress()">Hapus progres</button>
    </p>
</div>
@endsection
