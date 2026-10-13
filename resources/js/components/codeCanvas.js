// Komponen Alpine untuk halaman level: susun blok (termasuk Ulangi), jalankan, animasikan hasil engine.
import { run, countBlocks, MIN_REPEAT, MAX_REPEAT } from '../engine.js';
import { computeStars, computePoints } from '../scoring.js';
import { store } from '../store.js';

const SUPPORTED = ['forward', 'turn_left', 'turn_right', 'repeat'];
const LABELS = { forward: 'Maju', turn_left: 'Belok Kiri', turn_right: 'Belok Kanan', repeat: 'Ulangi' };
const DIR_DEG = { E: 0, S: 90, W: 180, N: 270 };
const MAX_NEST = 2; // Ulangi di dalam Ulangi: maksimal 2 tingkat (agar tetap mudah dibaca anak)

const STEP_MS = 520;
const STEP_MS_REDUCED = 160;

const MESSAGES = {
  success: 'Hore! Kiko sampai tujuan!',
  crash: 'Ups, Kiko menabrak! Coba lihat langkahmu lagi.',
  incomplete: 'Kiko belum sampai. Apa ya yang kurang?',
  too_long: 'Langkahnya terlalu panjang. Coba cari cara yang lebih singkat!',
  too_many_blocks: 'Blokmu terlalu banyak. Coba lebih hemat!',
  invalid: 'Ada blok yang tidak bisa dipakai di level ini.',
};

const prefersReducedMotion = () =>
  typeof window !== 'undefined' && !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/** Cari blok berdasarkan id di pohon program. */
function findNode(blocks, id, depth = 0) {
  for (const block of blocks) {
    if (block.id === id) return { block, list: blocks, depth };
    if (block.body) {
      const found = findNode(block.body, id, depth + 1);
      if (found) return found;
    }
  }
  return null;
}

export default function codeCanvas(level, nextUrl = null) {
  return {
    level,
    nextUrl,
    program: [],
    target: null, // id blok Ulangi yang sedang diisi; null = susunan utama
    seq: 0,
    pos: { x: level.start.x, y: level.start.y },
    rot: DIR_DEG[level.start.dir],
    face: level.start.dir === 'W' ? 'left' : 'right',
    running: false,
    activeId: null,
    bump: false,
    status: null,
    message: '',
    attempts: 0,  // berapa kali tombol Jalankan dipakai di kunjungan ini
    hints: 0,     // jumlah petunjuk Pipo yang dipakai (diisi oleh fitur Teman AI)
    result: null, // { stars, points, isNewBest } setelah menang
    best: null,   // rekor tersimpan untuk level ini

    init() {
      this.best = store.progress(this.level.slug);
    },

    // ---------- tampilan ----------
    get cells() {
      return this.level.grid.flatMap((row, y) =>
        row.map((tile, x) => ({
          key: `${x}-${y}`,
          tile,
          alt: (x + y) % 2 === 1,
          deco: tile === 0 && (x * 3 + y * 5) % 4 === 0,
          goal: x === this.level.goal.x && y === this.level.goal.y,
        })),
      );
    },
    get palette() {
      return this.level.allowed_blocks.filter((t) => SUPPORTED.includes(t));
    },
    get count() {
      return countBlocks(this.program);
    },
    get messageClass() {
      return this.status === 'success' ? 'msg-ok' : 'msg-try';
    },
    /** Daftar baris datar (dengan kedalaman) agar mudah dirender tanpa template rekursif. */
    get rows() {
      const out = [];
      let n = 0;
      const walk = (blocks, depth) => {
        for (const b of blocks) {
          if (b.type === 'repeat') {
            out.push({
              key: `${b.id}`, id: b.id, kind: 'repeat', type: 'repeat', depth, n: ++n,
              times: b.times, empty: b.body.length === 0, selected: this.target === b.id,
            });
            walk(b.body, depth + 1);
            out.push({ key: `${b.id}-end`, id: b.id, kind: 'end', type: 'end', depth });
          } else {
            out.push({ key: `${b.id}`, id: b.id, kind: 'block', type: b.type, depth, n: ++n });
          }
        }
      };
      walk(this.program, 0);
      return out;
    },
    label: (type) => LABELS[type] ?? type,
    minRepeat: MIN_REPEAT,
    maxRepeat: MAX_REPEAT,

    // ---------- menyusun blok ----------
    /** Tingkat sarang wadah tujuan: 0 = susunan utama, 1 = di dalam Ulangi, dst. */
    get targetLevel() {
      if (this.target === null) return 0;
      const node = findNode(this.program, this.target);
      return node ? node.depth + 1 : 0;
    },
    targetList() {
      if (this.target === null) return this.program;
      const node = findNode(this.program, this.target);
      if (!node) {
        this.target = null;
        return this.program;
      }
      return node.block.body;
    },
    selectTarget(id) {
      if (this.running) return;
      this.target = id;
    },
    add(type) {
      if (this.running) return;
      if (this.count >= this.level.max_blocks) {
        this.say('try', 'Blokmu sudah penuh. Hapus satu dulu, ya!');
        return;
      }
      if (type === 'repeat' && this.targetLevel >= MAX_NEST) {
        this.say('try', 'Ulangi di dalam Ulangi sudah cukup dalam. Taruh di luar dulu, ya!');
        return;
      }
      const block =
        type === 'repeat'
          ? { id: ++this.seq, type, times: MIN_REPEAT, body: [] }
          : { id: ++this.seq, type };
      this.targetList().push(block);
      if (type === 'repeat') this.target = block.id; // blok berikutnya masuk ke dalam Ulangi ini
      this.say(null, '');
    },
    setTimes(id, delta) {
      if (this.running) return;
      const node = findNode(this.program, id);
      if (!node || node.block.type !== 'repeat') return;
      node.block.times = Math.min(MAX_REPEAT, Math.max(MIN_REPEAT, node.block.times + delta));
    },
    remove(id) {
      if (this.running) return;
      const node = findNode(this.program, id);
      if (!node) return;
      node.list.splice(node.list.indexOf(node.block), 1);
      if (this.target !== null && !findNode(this.program, this.target)) this.target = null;
    },
    clearAll() {
      if (this.running) return;
      this.program = [];
      this.target = null;
      this.reset();
    },
    say(status, message) {
      this.status = status;
      this.message = message;
    },

    // ---------- Kiko ----------
    resetKiko() {
      this.pos = { x: this.level.start.x, y: this.level.start.y };
      // Putar lewat jalur terpendek agar tidak berputar balik berkali-kali.
      const target = DIR_DEG[this.level.start.dir];
      const diff = ((((target - this.rot) % 360) + 540) % 360) - 180;
      this.rot += diff;
      this.syncFace();
      this.activeId = null;
      this.bump = false;
    },
    // Karakter menghadap kiri/kanan (dibalik horizontal); untuk atas/bawah arah terakhir dipertahankan.
    syncFace() {
      const d = ((this.rot % 360) + 360) % 360;
      if (d === 180) this.face = 'left';
      else if (d === 0) this.face = 'right';
    },
    reset() {
      if (this.running) return;
      this.resetKiko();
      this.say(null, '');
    },

    // ---------- menjalankan ----------
    async play() {
      if (this.running) return;
      if (this.count === 0) {
        this.say('try', 'Susun dulu blokmu, ya! Ketuk blok di atas.');
        return;
      }

      // Salinan polos (tanpa proxy reaktif) untuk engine.
      const result = run(this.level, JSON.parse(JSON.stringify(this.program)));
      if (result.status === 'invalid' || result.status === 'too_many_blocks') {
        this.say('try', MESSAGES[result.status]);
        return;
      }

      this.running = true;
      this.attempts += 1;
      this.say(null, '');
      this.resetKiko();
      const stepMs = prefersReducedMotion() ? STEP_MS_REDUCED : STEP_MS;
      await sleep(stepMs);

      for (const step of result.steps) {
        this.activeId = step.blockId;
        if (step.crashed) {
          this.bump = true;
          await sleep(stepMs);
          this.bump = false;
          break;
        }
        this.pos = { x: step.x, y: step.y };
        if (step.action === 'turn_left') this.rot -= 90;
        if (step.action === 'turn_right') this.rot += 90;
        this.syncFace();
        await sleep(stepMs);
      }

      this.activeId = null;
      if (result.status === 'success') this.finish();
      this.say(result.status, MESSAGES[result.status] ?? '');
      if (result.status === 'crash') {
        await sleep(stepMs);
        this.resetKiko();
      }
      this.running = false;
    },

    /** Hitung bintang & poin, lalu simpan rekor ke localStorage. */
    finish() {
      const stars = computeStars({ blocks: this.count, optimal: this.level.optimal_blocks, hints: this.hints });
      const points = computePoints({ stars, attempts: this.attempts });
      const { entry, isNewBest } = store.recordResult(this.level.slug, {
        stars, blocks: this.count, hints: this.hints, attempts: this.attempts, points,
      });
      this.result = { stars, points, isNewBest };
      this.best = entry;
    },
  };
}
