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
                    <template x-for="row in rows" :key="row.key">
                        <li :style="`margin-left:${row.depth * 1.25}rem`">
                            {{-- blok biasa --}}
                            <div x-show="row.kind === 'block'" class="chip" :class="['blk-' + row.type, activeId === row.id ? 'is-active' : '']">
                                <span class="flex items-center gap-2">
                                    <span class="grid h-7 w-7 place-items-center rounded-full bg-ink text-sm text-paper" x-text="row.n"></span>
                                    <svg class="ico" aria-hidden="true"><use :href="'#i-' + row.type"/></svg>
                                    <span x-text="label(row.type)"></span>
                                </span>
                                <button type="button" class="chip-x" @click="remove(row.id)" :disabled="running" aria-label="Hapus blok ini">
                                    <svg class="ico" aria-hidden="true"><use href="#i-close"/></svg>
                                </button>
                            </div>

                            {{-- awal blok Ulangi --}}
                            <div x-show="row.kind === 'repeat'" class="chip chip-repeat blk-repeat" :class="{ 'is-target': row.selected }">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="grid h-7 w-7 place-items-center rounded-full bg-ink text-sm text-paper" x-text="row.n"></span>
                                    <button type="button" class="repeat-label" @click="selectTarget(row.id)" :aria-pressed="row.selected" aria-label="Isi blok Ulangi ini">
                                        <svg class="ico" aria-hidden="true"><use href="#i-repeat"/></svg> Ulangi
                                    </button>
                                    <span class="stepper">
                                        <button type="button" @click="setTimes(row.id, -1)" :disabled="running || row.times <= minRepeat" aria-label="Kurangi pengulangan"><svg class="ico" aria-hidden="true"><use href="#i-minus"/></svg></button>
                                        <output x-text="row.times" aria-live="polite"></output>
                                        <button type="button" @click="setTimes(row.id, 1)" :disabled="running || row.times >= maxRepeat" aria-label="Tambah pengulangan"><svg class="ico" aria-hidden="true"><use href="#i-plus"/></svg></button>
                                    </span>
                                    <span>kali</span>
                                </span>
                                <button type="button" class="chip-x" @click="remove(row.id)" :disabled="running" aria-label="Hapus blok Ulangi ini">
                                    <svg class="ico" aria-hidden="true"><use href="#i-close"/></svg>
                                </button>
                            </div>
                            <p x-show="row.kind === 'repeat' && row.empty" class="hint-empty">Ketuk blok di atas untuk memasukkannya ke sini.</p>

                            {{-- awal blok Jika Ada Rintangan --}}
                            <div x-show="row.kind === 'if'" class="chip chip-repeat blk-if_obstacle" :class="{ 'is-target': row.selected }">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="grid h-7 w-7 place-items-center rounded-full bg-ink text-sm text-paper" x-text="row.n"></span>
                                    <button type="button" class="repeat-label" @click="selectTarget(row.id, 'body')" :aria-pressed="row.selected" aria-label="Isi bagian Maka">
                                        <svg class="ico" aria-hidden="true"><use href="#i-if_obstacle"/></svg> Jika ada rintangan di depan, maka:
                                    </button>
                                </span>
                                <button type="button" class="chip-x" @click="remove(row.id)" :disabled="running" aria-label="Hapus blok Jika ini">
                                    <svg class="ico" aria-hidden="true"><use href="#i-close"/></svg>
                                </button>
                            </div>
                            <p x-show="row.kind === 'if' && row.empty" class="hint-empty">Ketuk blok di atas untuk memasukkannya ke bagian Maka.</p>

                            {{-- bagian "Kalau tidak" --}}
                            <div x-show="row.kind === 'else'" class="chip chip-else" :class="{ 'is-target': row.selected }">
                                <button type="button" class="repeat-label" @click="selectTarget(row.id, 'else')" :aria-pressed="row.selected" aria-label="Isi bagian Kalau tidak">
                                    Kalau tidak:
                                </button>
                            </div>
                            <p x-show="row.kind === 'else' && row.empty" class="hint-empty">Ketuk blok di atas untuk memasukkannya ke bagian Kalau tidak.</p>

                            {{-- penutup blok Ulangi --}}
                            <div x-show="row.kind === 'end'" class="chip-end" aria-hidden="true"></div>
                        </li>
                    </template>
                    <li x-show="count === 0" class="p-4 text-center opacity-70">Ketuk blok di atas untuk mulai menyusun.</li>
                </ol>

                <p x-show="target !== null" class="target-note">
                    <span x-text="targetSlot === 'else' ? 'Blok baru masuk ke bagian Kalau tidak.' : 'Blok baru masuk ke dalam wadah yang dipilih.'"></span>
                    <button type="button" class="link-btn" @click="selectTarget(null)">Pindah ke luar</button>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="btn btn-run" @click="play()" :disabled="running || count === 0">
                    <svg class="ico" aria-hidden="true"><use href="#i-play"/></svg> Jalankan
                </button>
                <button type="button" class="btn" @click="reset()" :disabled="running">
                    <svg class="ico" aria-hidden="true"><use href="#i-reset"/></svg> Dari awal
                </button>
                <button type="button" class="btn blk-forward" @click="askPipo()" :disabled="running || pipo.loading">
                    <svg class="h-7 w-7" viewBox="0 0 64 64" aria-hidden="true"><use href="#pipo"/></svg> Tanya Pipo
                </button>
                <button type="button" class="btn" @click="clearAll()" :disabled="running || count === 0">
                    <svg class="ico" aria-hidden="true"><use href="#i-trash"/></svg> Hapus semua
                </button>
            </div>

            <div x-show="pipo.open" x-transition.duration.250ms class="flex items-start gap-3" aria-live="polite">
                <svg class="h-16 w-16 shrink-0" viewBox="0 0 64 64" aria-hidden="true"><use href="#pipo"/></svg>
                <div class="bubble ml-2 grow">
                    <p x-show="pipo.loading" class="font-display font-semibold">Pipo sedang berpikir...</p>
                    <p x-show="!pipo.loading" x-text="pipo.text"></p>
                    <p class="mt-2 text-sm opacity-70">
                        Petunjuk <span x-text="hints"></span>/5. Memakai petunjuk bisa mengurangi bintang.
                    </p>
                </div>
            </div>

            <div aria-live="polite" role="status" class="grid gap-3">
                <p x-show="message" x-transition.duration.250ms class="msg" :class="messageClass" x-text="message"></p>
                <template x-if="status === 'success' && result">
                    <div class="card grid gap-3 p-4">
                        <div class="flex gap-2" role="img" :aria-label="result.stars + ' dari 3 bintang'">
                            <template x-for="n in 3" :key="n">
                                <svg class="star" :class="{ on: result.stars >= n }" :style="`--i:${n}`" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-star"/></svg>
                            </template>
                        </div>
                        <p class="flex flex-wrap items-center gap-2 font-display text-xl font-extrabold">
                            +<span x-text="result.points"></span> poin
                            <span x-show="result.isNewBest" class="tag">Rekor baru!</span>
                        </p>
                        <p>Kamu memakai <span x-text="count"></span> blok (paling hemat: <span x-text="level.optimal_blocks"></span>).</p>
                        <div class="flex flex-wrap gap-3">
                            <a x-show="nextUrl" :href="nextUrl" class="btn btn-sun">
                                Level berikutnya <svg class="ico" aria-hidden="true"><use href="#i-next"/></svg>
                            </a>
                            <a href="{{ route('map') }}" class="btn">
                                <svg class="ico" aria-hidden="true"><use href="#i-map"/></svg> Peta
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </div>
</div>
@endsection
