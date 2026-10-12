@extends('layouts.app')

@section('title', 'Level ' . $level['slug'] . ': ' . $level['title'] . ' | Petualangan Kode')

@section('content')
<div x-data="codeCanvas(@js($level), @js($nextUrl))">

    <nav class="flex flex-wrap items-center gap-3" aria-label="Level di dunia ini">
        <span class="font-display text-xl font-extrabold">Dunia {{ $level['world'] }}</span>
        @foreach ($siblings as $s)
            <a href="{{ $s['url'] }}" class="pill {{ $s['slug'] === $level['slug'] ? 'pill-current' : '' }}"
               @if ($s['slug'] === $level['slug']) aria-current="page" @endif>{{ $s['slug'] }}</a>
        @endforeach
    </nav>

    <div class="mt-6 grid gap-10 lg:grid-cols-[auto_1fr] lg:items-start">

        {{-- Cerita + panggung --}}
        <section aria-labelledby="judul-level" class="grid gap-6">
            <div class="flex items-start gap-4">
                <svg class="h-20 w-20 shrink-0" viewBox="0 0 64 64" aria-hidden="true"><use href="#pipo"/></svg>
                <div class="bubble ml-2">
                    <h1 id="judul-level" class="text-2xl font-extrabold sm:text-3xl">{{ $level['title'] }}</h1>
                    <p class="mt-1">{{ $level['story'] }}</p>
                </div>
            </div>

            <div class="stage" role="img" aria-label="Peta level. Kiko mulai dari petak awal, bendera merah adalah tujuan.">
                <div class="stage-grid" :style="`grid-template-columns: repeat(${level.grid_width}, 1fr)`">
                    <template x-for="cell in cells" :key="cell.key">
                        <div class="tile" :class="['tile-' + cell.tile, cell.alt ? 'alt' : '']">
                            <svg x-show="cell.tile > 0 || cell.deco" class="tile-art" viewBox="0 0 48 48" aria-hidden="true"><use :href="'#t' + cell.tile"/></svg>
                            <svg x-show="cell.goal" class="tile-art" viewBox="0 0 48 48" aria-hidden="true"><use href="#flag"/></svg>
                        </div>
                    </template>
                </div>

                <div class="kiko"
                     :style="`width:${100 / level.grid_width}%;height:${100 / level.grid_height}%;transform:translate(${pos.x * 100}%,${pos.y * 100}%)`">
                    <div class="kiko-dir" :style="`transform: rotate(${rot}deg)`"></div>
                    <div class="kiko-anim" :class="{ 'is-bump': bump, 'is-win': status === 'success' }">
                        <svg class="kiko-sprite" :class="{ 'is-flipped': face === 'left' }" viewBox="0 0 64 64" aria-hidden="true"><use href="#kiko"/></svg>
                    </div>
                </div>

                <div class="confetti" x-show="status === 'success'" aria-hidden="true">
                    @for ($i = 0; $i < 18; $i++)
                        <i style="--x: {{ ($i * 37 + 5) % 96 }}%; --d: {{ ($i % 6) * 0.12 }}s; --r: {{ ($i * 47) % 180 }}deg; --c: var(--color-{{ ['ruby', 'sun', 'blush', 'paper'][$i % 4] }})"></i>
                    @endfor
                </div>
            </div>
        </section>

        {{-- Blok + kanvas susun --}}
        <section class="grid gap-7" aria-label="Susun perintah">
            <div>
                <h2 class="text-2xl font-extrabold">Pilih blok</h2>
                <div class="mt-3 flex flex-wrap gap-3">
                    <template x-for="type in palette" :key="type">
                        <button type="button" class="btn" :class="'blk-' + type" @click="add(type)" :disabled="running">
                            <svg class="ico" aria-hidden="true"><use :href="'#i-' + type"/></svg>
                            <span x-text="label(type)"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div>
                <div class="flex items-baseline justify-between">
                    <h2 class="text-2xl font-extrabold">Susunanmu</h2>
                    <p class="tag" aria-live="polite"><span x-text="count"></span>/<span x-text="level.max_blocks"></span> blok</p>
                </div>

                <ol class="card mt-3 grid min-h-28 content-start gap-2.5 p-3" aria-label="Urutan blokmu">
                    <template x-for="(block, i) in program" :key="block.id">
                        <li class="chip" :class="['blk-' + block.type, activeId === block.id ? 'is-active' : '']">
                            <span class="flex items-center gap-2">
                                <span class="grid h-7 w-7 place-items-center rounded-full bg-ink text-sm text-paper" x-text="i + 1"></span>
                                <svg class="ico" aria-hidden="true"><use :href="'#i-' + block.type"/></svg>
                                <span x-text="label(block.type)"></span>
                            </span>
                            <button type="button" class="chip-x" @click="remove(block.id)" :disabled="running" aria-label="Hapus blok ini">
                                <svg class="ico" aria-hidden="true"><use href="#i-close"/></svg>
                            </button>
                        </li>
                    </template>
                    <li x-show="count === 0" class="p-4 text-center opacity-70">Ketuk blok di atas untuk mulai menyusun.</li>
                </ol>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="btn btn-run" @click="play()" :disabled="running || count === 0">
                    <svg class="ico" aria-hidden="true"><use href="#i-play"/></svg> Jalankan
                </button>
                <button type="button" class="btn" @click="reset()" :disabled="running">
                    <svg class="ico" aria-hidden="true"><use href="#i-reset"/></svg> Dari awal
                </button>
                <button type="button" class="btn" @click="clearAll()" :disabled="running || count === 0">
                    <svg class="ico" aria-hidden="true"><use href="#i-trash"/></svg> Hapus semua
                </button>
            </div>

            <div aria-live="polite" role="status" class="grid gap-3">
                <p x-show="message" x-transition.duration.250ms class="msg" :class="messageClass" x-text="message"></p>
                <template x-if="status === 'success'">
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="font-display text-lg font-semibold">
                            Kamu memakai <span x-text="count"></span> blok (paling hemat: <span x-text="level.optimal_blocks"></span>).
                        </p>
                        <a x-show="nextUrl" :href="nextUrl" class="btn btn-sun">
                            Level berikutnya <svg class="ico" aria-hidden="true"><use href="#i-next"/></svg>
                        </a>
                    </div>
                </template>
            </div>
        </section>
    </div>
</div>
@endsection
