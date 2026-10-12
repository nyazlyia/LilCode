// Jalankan: node --test tests/js/canvas.test.js
// Menguji komponen Alpine tanpa browser (logika menyusun & menjalankan blok).
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

globalThis.window = { matchMedia: () => ({ matches: true }) }; // gerakan dikurangi -> tes cepat
const { default: codeCanvas } = await import('../../resources/js/components/codeCanvas.js');

const levels = JSON.parse(readFileSync(new URL('../../database/data/levels.json', import.meta.url), 'utf8'));
const bySlug = Object.fromEntries(levels.map((l) => [l.slug, l]));
const pub = ({ solution, solution_outline, ...rest }) => rest; // seperti yang dikirim server ke browser

/** Susun program (pohon) lewat API komponen, persis seperti anak mengetuk tombol. */
function build(c, blocks) {
  for (const b of blocks) {
    const parent = c.target; // wadah tempat blok ini ditaruh
    c.add(b.type);
    if (b.type === 'repeat') {
      const id = c.target;
      c.setTimes(id, b.times - 2);
      build(c, b.body);
      c.selectTarget(parent); // kembali ke wadah induk
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

test('Ulangi: sarang dibatasi 2 tingkat', () => {
  const c = codeCanvas(pub(bySlug['2-2']), null);
  c.add('repeat'); // tingkat 1
  c.add('repeat'); // tingkat 2
  const before = c.count;
  c.add('repeat'); // ditolak
  assert.equal(c.count, before);
  assert.match(c.message, /cukup dalam/);
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
