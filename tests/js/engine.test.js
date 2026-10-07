// Jalankan: node --test tests/js/engine.test.js
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { run, countBlocks, validateProgram, MAX_STEPS } from '../../resources/js/engine.js';

const levels = JSON.parse(readFileSync(new URL('../../database/data/levels.json', import.meta.url), 'utf8'));
const bySlug = Object.fromEntries(levels.map((l) => [l.slug, l]));
const F = { type: 'forward' };
const L = { type: 'turn_left' };
const R = { type: 'turn_right' };

for (const level of levels) {
  test(`level ${level.slug}: solusi referensi berhasil dan efisien`, () => {
    const out = run(level, level.solution);
    assert.equal(out.status, 'success');
    assert.equal(countBlocks(level.solution), level.optimal_blocks);
    assert.ok(level.optimal_blocks <= level.max_blocks);
    assert.equal(level.grid.length, level.grid_height);
    assert.equal(level.grid[0].length, level.grid_width);
  });
}

test('belok kiri dan kanan mengubah arah dengan benar', () => {
  const out = run(bySlug['1-2'], [L]);
  assert.equal(out.final.dir, 'N');
  assert.equal(run(bySlug['1-2'], [R]).final.dir, 'S');
  assert.equal(run(bySlug['1-2'], [L, L, L, L]).final.dir, 'E');
});

test('menabrak batu -> crash, posisi tetap dan langkah ditandai', () => {
  const out = run(bySlug['1-3'], [F, F]);
  assert.equal(out.status, 'crash');
  assert.deepEqual(out.final, { x: 1, y: 2, dir: 'E' });
  assert.equal(out.steps.at(-1).crashed, true);
});

test('keluar peta -> crash', () => {
  assert.equal(run(bySlug['1-2'], [R, F]).status, 'crash'); // dari (0,4) menghadap bawah
});

test('program selesai sebelum tujuan -> incomplete', () => {
  assert.equal(run(bySlug['1-1'], [F, F]).status, 'incomplete');
  assert.equal(run(bySlug['1-1'], []).status, 'incomplete');
});

test('berhenti begitu sampai tujuan (blok sisa diabaikan)', () => {
  const out = run(bySlug['1-1'], [F, F, F, F]);
  assert.equal(out.status, 'success');
  assert.equal(out.steps.length, 3);
});

test('blok tidak diizinkan -> invalid', () => {
  const out = run(bySlug['1-1'], [L]);
  assert.equal(out.status, 'invalid');
});

test('blok melebihi batas -> too_many_blocks', () => {
  assert.equal(run(bySlug['1-1'], [F, F, F, F, F, F]).status, 'too_many_blocks');
});

test('struktur rusak -> invalid (bukan error)', () => {
  assert.equal(run(bySlug['1-1'], null).status, 'invalid');
  assert.equal(run(bySlug['1-1'], [{ type: 'hack' }]).status, 'invalid');
});

// Level buatan untuk menguji repeat dan if_obstacle
const free = (extra = {}) => ({
  grid: Array.from({ length: 5 }, () => [0, 0, 0, 0, 0]),
  start: { x: 0, y: 0, dir: 'E' },
  goal: { x: 4, y: 0 },
  allowed_blocks: ['forward', 'turn_left', 'turn_right', 'repeat', 'if_obstacle'],
  max_blocks: 10,
  ...extra,
});

test('repeat: Ulangi 4x Maju mencapai tujuan dengan 2 blok', () => {
  const prog = [{ type: 'repeat', times: 4, body: [F] }];
  const out = run(free(), prog);
  assert.equal(out.status, 'success');
  assert.equal(out.blocksUsed, 2);
  assert.equal(out.steps.length, 4);
});

test('repeat: jumlah ulangan di luar 2..9 -> invalid', () => {
  assert.equal(run(free(), [{ type: 'repeat', times: 1, body: [F] }]).status, 'invalid');
  assert.equal(run(free(), [{ type: 'repeat', times: 10, body: [F] }]).status, 'invalid');
});

test('if_obstacle: berbelok jika terhalang, maju jika tidak', () => {
  const level = free({ goal: { x: 2, y: 1 } });
  level.grid[0][3] = 1; // batu di (3,0)
  const prog = [{ type: 'repeat', times: 5, body: [{ type: 'if_obstacle', body: [R], else_body: [F] }] }];
  const out = run(level, prog);
  assert.equal(out.status, 'success');
  assert.deepEqual(out.final, { x: 2, y: 1, dir: 'S' });
});

test('terlalu panjang -> too_long (pengaman langkah)', () => {
  const inner = { type: 'repeat', times: 9, body: [L] };
  const mid = { type: 'repeat', times: 9, body: [inner] };
  const prog = [{ type: 'repeat', times: 9, body: [mid] }]; // 729 putaran > MAX_STEPS
  const out = run(free(), prog);
  assert.equal(out.status, 'too_long');
  assert.ok(out.steps.length <= MAX_STEPS);
});

test('bersarang terlalu dalam -> invalid', () => {
  const deep = [{ type: 'repeat', times: 2, body: [{ type: 'repeat', times: 2, body: [{ type: 'repeat', times: 2, body: [{ type: 'repeat', times: 2, body: [F] }] }] }] }];
  assert.equal(validateProgram(free({ max_blocks: 20 }), deep).ok, false);
});

test('blockId diteruskan ke langkah untuk penyorotan UI', () => {
  const out = run(bySlug['1-1'], [{ ...F, id: 'a' }, { ...F, id: 'b' }, { ...F, id: 'c' }]);
  assert.deepEqual(out.steps.map((s) => s.blockId), ['a', 'b', 'c']);
});
