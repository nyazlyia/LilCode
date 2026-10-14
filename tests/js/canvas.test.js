// Jalankan: node --test tests/js/canvas.test.js
// Menguji komponen Alpine tanpa browser (logika menyusun & menjalankan blok).
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

// Stub browser: gerakan dikurangi (tes cepat) dan localStorage palsu.
const memory = {};
globalThis.window = {
  matchMedia: () => ({ matches: true }),
  localStorage: {
    getItem: (k) => (k in memory ? memory[k] : null),
    setItem: (k, v) => { memory[k] = String(v); },
  },
};
const { default: codeCanvas } = await import('../../resources/js/components/codeCanvas.js');
const { store } = await import('../../resources/js/store.js');

const levels = JSON.parse(readFileSync(new URL('../../database/data/levels.json', import.meta.url), 'utf8'));
const bySlug = Object.fromEntries(levels.map((l) => [l.slug, l]));
const pub = ({ solution, solution_outline, ...rest }) => rest; // seperti yang dikirim server ke browser

/** Susun program (pohon) lewat API komponen, persis seperti anak mengetuk tombol. */
function build(c, blocks) {
  for (const b of blocks) {
    const parent = c.target; // wadah tempat blok ini ditaruh
    const parentSlot = c.targetSlot;
    c.add(b.type);
    if (b.type === 'repeat') {
      const id = c.target;
      c.setTimes(id, b.times - 2);
      build(c, b.body);
      c.selectTarget(parent, parentSlot);
    } else if (b.type === 'if_obstacle') {
      const id = c.target;
      build(c, b.body);
      c.selectTarget(id, 'else');
      build(c, b.else_body ?? []);
      c.selectTarget(parent, parentSlot);
    }
  }
}

for (const level of levels) {
  test(`level ${level.slug}: dapat dimainkan sampai menang lewat komponen`, async () => {
    const c = codeCanvas(pub(level), '/next');
    assert.equal(c.cells.length, level.grid_width * level.grid_height);
    build(c, level.solution);
    assert.equal(c.count, level.optimal_blocks);
    await c.play();
    assert.equal(c.status, 'success');
    assert.deepEqual(c.pos, level.goal);
    assert.equal(c.running, false);
  });
}

test('menabrak -> Kiko kembali ke awal', async () => {
  const level = bySlug['1-3'];
  const c = codeCanvas(pub(level), null);
  c.add('forward');
  c.add('forward');
  await c.play();
  assert.equal(c.status, 'crash');
  assert.deepEqual(c.pos, { x: level.start.x, y: level.start.y });
});

test('program kosong, batas blok, hapus, dan hapus semua', async () => {
  const level = bySlug['1-1'];
  const c = codeCanvas(pub(level), null);
  await c.play();
  assert.match(c.message, /Susun dulu/);
  for (let i = 0; i < 7; i++) c.add('forward');
  assert.equal(c.count, level.max_blocks);
  assert.match(c.message, /penuh/);
  c.remove(c.program[0].id);
  assert.equal(c.count, level.max_blocks - 1);
  c.clearAll();
  assert.equal(c.count, 0);
});

test('Ulangi: blok baru masuk ke wadah terpilih, bisa pindah ke luar', () => {
  const c = codeCanvas(pub(bySlug['2-1']), null);
  c.add('repeat');
  const rid = c.target;
  c.add('forward');
  assert.equal(c.program.length, 1);
  assert.equal(c.program[0].body.length, 1);
  c.selectTarget(null);
  c.add('turn_left');
  assert.equal(c.program.length, 2);
  assert.deepEqual(c.rows.map((r) => r.kind), ['repeat', 'block', 'end', 'block']);
  c.selectTarget(rid);
  assert.equal(c.targetLevel, 1);
});

test('Ulangi: jumlah dibatasi 2..9', () => {
  const c = codeCanvas(pub(bySlug['2-1']), null);
  c.add('repeat');
  const id = c.target;
  c.setTimes(id, -5);
  assert.equal(c.program[0].times, 2);
  c.setTimes(id, 20);
  assert.equal(c.program[0].times, 9);
});

test('wadah bersarang dibatasi 3 tingkat', () => {
  const c = codeCanvas(pub(bySlug['3-5']), null);
  c.add('repeat'); // tingkat 1
  c.add('repeat'); // tingkat 2
  c.add('if_obstacle'); // tingkat 3
  assert.equal(c.count, 3);
  c.add('repeat'); // ditolak: terlalu dalam
  assert.equal(c.count, 3);
  assert.match(c.message, /cukup dalam/);
  c.add('forward'); // blok biasa tetap boleh di dalam tingkat 3
  assert.equal(c.count, 4);
});

test('menghapus Ulangi yang sedang dipilih mengembalikan target ke susunan utama', () => {
  const c = codeCanvas(pub(bySlug['2-1']), null);
  c.add('repeat');
  c.add('forward');
  c.remove(c.target);
  assert.equal(c.target, null);
  assert.equal(c.count, 0);
});

test('level Dunia 1 tidak menampilkan blok Ulangi di palet', () => {
  assert.deepEqual(codeCanvas(pub(bySlug['1-1']), null).palette, ['forward']);
  assert.ok(codeCanvas(pub(bySlug['2-1']), null).palette.includes('repeat'));
});

test('menang: bintang & poin dihitung dan progres tersimpan di localStorage', async () => {
  const level = bySlug['1-1'];
  const c = codeCanvas(pub(level), '/next');
  c.init();
  build(c, level.solution);
  await c.play();
  assert.equal(c.status, 'success');
  assert.equal(c.result.stars, 3);
  assert.equal(c.attempts, 1);
  assert.equal(c.result.points, 270);
  assert.equal(store.progress('1-1').stars, 3);
  assert.ok(memory['petualangan-kode:v1'], 'tertulis ke localStorage');
});

test('dua kali jalan: bonus percobaan pertama hilang, rekor tidak turun', async () => {
  const level = bySlug['1-2'];
  const c = codeCanvas(pub(level), null);
  c.init();
  c.add('forward'); // sengaja salah dulu (belum sampai)
  await c.play();
  assert.equal(c.status, 'incomplete');
  c.clearAll();
  build(c, level.solution);
  await c.play();
  assert.equal(c.attempts, 2);
  assert.equal(c.result.points, 250); // 100 + 150, tanpa bonus 20
  assert.equal(c.result.stars, 3);
});

test('program yang ditolak engine tidak dihitung sebagai percobaan', async () => {
  const c = codeCanvas(pub(bySlug['1-1']), null);
  for (let i = 0; i < 5; i++) c.add('forward');
  c.program.push({ id: 99, type: 'turn_left' }); // blok tidak diizinkan, disusupkan manual
  await c.play();
  assert.equal(c.attempts, 0);
});

test('Jika Ada Rintangan: bagian Maka dan Kalau tidak terisi terpisah', () => {
  const c = codeCanvas(pub(bySlug['3-1']), null);
  c.add('if_obstacle');
  const id = c.target;
  c.add('turn_right'); // masuk ke Maka
  c.selectTarget(id, 'else');
  c.add('forward'); // masuk ke Kalau tidak
  const node = c.program[0];
  assert.equal(node.body.length, 1);
  assert.equal(node.body[0].type, 'turn_right');
  assert.equal(node.else_body.length, 1);
  assert.equal(node.else_body[0].type, 'forward');
  assert.deepEqual(c.rows.map((r) => r.kind), ['if', 'block', 'else', 'block', 'end']);
  assert.equal(c.rows.find((r) => r.kind === 'else').selected, true);
});

test('menghapus blok Jika membuang isinya dan mengembalikan target ke luar', () => {
  const c = codeCanvas(pub(bySlug['3-1']), null);
  c.add('if_obstacle');
  const id = c.target;
  c.add('forward');
  c.remove(id);
  assert.equal(c.count, 0);
  assert.equal(c.target, null);
  assert.equal(c.targetSlot, 'body');
});

test('palet: blok Jika hanya muncul di Dunia 3', () => {
  assert.ok(codeCanvas(pub(bySlug['3-1']), null).palette.includes('if_obstacle'));
  assert.ok(!codeCanvas(pub(bySlug['2-1']), null).palette.includes('if_obstacle'));
});

// ---------- Teman AI: Pipo ----------
function fakeFetch(handler) {
  const calls = [];
  globalThis.fetch = async (url, options) => {
    const body = JSON.parse(options.body);
    calls.push({ url, options, body });
    return handler(body, calls.length);
  };
  return calls;
}
const okReply = (hint) => ({ ok: true, json: async () => ({ hint, source: 'ai' }) });

test('Pipo: tingkat petunjuk naik 1 -> 2 -> 3 dan tertahan di 3', async () => {
  const calls = fakeFetch((_, n) => okReply(`petunjuk ${n}`));
  const c = codeCanvas(pub(bySlug['2-1']), null);
  for (let i = 0; i < 4; i++) await c.askPipo();
  assert.deepEqual(calls.map((x) => x.body.tier), [1, 2, 3, 3]);
  assert.equal(c.hints, 4);
  assert.equal(c.pipo.text, 'petunjuk 4');
  assert.equal(c.pipo.open, true);
  assert.equal(c.pipo.loading, false);
});

test('Pipo: dibatasi 5 petunjuk per level, setelah itu tidak memanggil server', async () => {
  const calls = fakeFetch(() => okReply('ok'));
  const c = codeCanvas(pub(bySlug['2-2']), null);
  for (let i = 0; i < 7; i++) await c.askPipo();
  assert.equal(calls.length, 5);
  assert.equal(c.hints, 5);
  assert.equal(c.hintsLeft, 0);
  assert.match(c.pipo.text, /5 petunjuk/);
});

test('Pipo: gagal jaringan, HTTP error, atau respons aneh tidak dihitung sebagai petunjuk', async () => {
  const c = codeCanvas(pub(bySlug['2-3']), null);

  globalThis.fetch = async () => { throw new Error('offline'); };
  await c.askPipo();
  assert.equal(c.hints, 0);
  assert.match(c.pipo.text, /istirahat/);

  globalThis.fetch = async () => ({ ok: false, status: 500, json: async () => ({}) });
  await c.askPipo();
  assert.equal(c.hints, 0);

  globalThis.fetch = async () => ({ ok: true, json: async () => ({ nope: 1 }) });
  await c.askPipo();
  assert.equal(c.hints, 0);
  assert.equal(c.pipo.loading, false);

  fakeFetch(() => okReply('Coba lagi, ya!'));
  await c.askPipo();
  assert.equal(c.hints, 1);
});

test('Pipo: payload memuat level, program polos, status terakhir, dan tidak memuat solusi', async () => {
  const calls = fakeFetch(() => okReply('ok'));
  const level = bySlug['2-1'];
  const c = codeCanvas({ ...pub(level), solution: 'RAHASIA' }, null); // bahkan jika data bocor, payload tidak menyertakannya
  c.add('forward');
  await c.play(); // 1 langkah, belum sampai -> incomplete
  await c.askPipo();
  const { body, url, options } = calls[0];
  assert.equal(url, '/api/mentor');
  assert.equal(options.method, 'POST');
  assert.equal(body.level, '2-1');
  assert.equal(body.status, 'incomplete');
  assert.equal(body.tier, 1);
  assert.deepEqual(body.program.map((b) => b.type), ['forward']);
  assert.ok(!JSON.stringify(body).includes('RAHASIA'));
});

test('Pipo: status invalid tidak dikirim, dan tidak bisa dipakai saat Kiko berjalan', async () => {
  const calls = fakeFetch(() => okReply('ok'));
  const c = codeCanvas(pub(bySlug['1-1']), null);
  c.add('forward');
  c.program.push({ id: 99, type: 'turn_left' }); // tidak diizinkan -> status invalid
  await c.play();
  assert.equal(c.lastStatus, 'invalid');
  await c.askPipo();
  assert.equal(calls[0].body.status, null);

  c.running = true;
  await c.askPipo();
  assert.equal(calls.length, 1); // tidak memanggil saat running
});

test('Pipo: memakai petunjuk mengurangi bintang', async () => {
  fakeFetch(() => okReply('ok'));
  const level = bySlug['1-1'];
  const c = codeCanvas(pub(level), null);
  c.init();
  await c.askPipo();
  await c.askPipo();
  build(c, level.solution);
  await c.play();
  assert.equal(c.status, 'success');
  assert.equal(c.result.stars, 2); // optimal tercapai, tetapi 2 petunjuk dipakai
});
