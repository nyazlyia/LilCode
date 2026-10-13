// Jalankan: node --test tests/js/store.test.js
import test from 'node:test';
import assert from 'node:assert/strict';
import { createStore } from '../../resources/js/store.js';
import { computeStars, computePoints } from '../../resources/js/scoring.js';

/** localStorage palsu. */
const fakeStorage = (initial = {}) => {
  const data = { ...initial };
  return {
    data,
    getItem: (k) => (k in data ? data[k] : null),
    setItem: (k, v) => { data[k] = String(v); },
  };
};
const KEY = 'petualangan-kode:v1';
const order = ['1-1', '1-2', '1-3'];

test('bintang: 3 jika hemat tanpa petunjuk, 2 jika agak boros, 1 jika boros dan banyak petunjuk', () => {
  assert.equal(computeStars({ blocks: 3, optimal: 3, hints: 0 }), 3);
  assert.equal(computeStars({ blocks: 3, optimal: 3, hints: 1 }), 2);
  assert.equal(computeStars({ blocks: 5, optimal: 3, hints: 0 }), 2);
  assert.equal(computeStars({ blocks: 9, optimal: 3, hints: 3 }), 1);
});

test('poin: 100 + 50 per bintang + 20 bonus percobaan pertama', () => {
  assert.equal(computePoints({ stars: 3, attempts: 1 }), 270);
  assert.equal(computePoints({ stars: 3, attempts: 2 }), 250);
  assert.equal(computePoints({ stars: 1, attempts: 4 }), 150);
});

test('progres tersimpan dan terbaca kembali setelah "refresh"', () => {
  const storage = fakeStorage();
  const a = createStore(() => storage);
  a.recordResult('1-1', { stars: 3, blocks: 3, hints: 0, attempts: 1, points: 270 });

  const b = createStore(() => storage); // simulasi muat ulang halaman
  assert.equal(b.progress('1-1').stars, 3);
  assert.equal(b.state().points, 270);
  assert.ok(b.state().player.id);
});

test('mengulang level tidak menurunkan rekor dan poin tidak bisa di-farming', () => {
  const s = createStore(() => fakeStorage());
  s.recordResult('1-1', { stars: 3, blocks: 3, hints: 0, attempts: 1, points: 270 });
  const again = s.recordResult('1-1', { stars: 1, blocks: 5, hints: 2, attempts: 6, points: 150 });
  assert.equal(again.isNewBest, false);
  assert.equal(s.progress('1-1').stars, 3);
  assert.equal(s.progress('1-1').bestBlocks, 3);
  assert.equal(s.state().points, 270);

  const better = s.recordResult('1-2', { stars: 1, blocks: 9, hints: 0, attempts: 3, points: 150 });
  assert.equal(better.firstTime, true);
  s.recordResult('1-2', { stars: 3, blocks: 7, hints: 0, attempts: 1, points: 270 });
  assert.equal(s.state().points, 540); // jumlah rekor terbaik per level
  assert.equal(s.totalStars(), 6);
});

test('level terbuka berurutan', () => {
  const s = createStore(() => fakeStorage());
  assert.equal(s.isUnlocked('1-1', order), true);
  assert.equal(s.isUnlocked('1-2', order), false);
  s.recordResult('1-1', { stars: 1, blocks: 4, hints: 0, attempts: 1, points: 170 });
  assert.equal(s.isUnlocked('1-2', order), true);
  assert.equal(s.isUnlocked('1-3', order), false);
  assert.equal(s.isUnlocked('9-9', order), false);
});

test('data rusak atau versi lain diabaikan tanpa error', () => {
  for (const bad of ['{bukan json', '{"version":99,"progress":{}}', 'null', '[]']) {
    const s = createStore(() => fakeStorage({ [KEY]: bad }));
    assert.deepEqual(s.state().progress, {});
  }
});

test('localStorage tidak tersedia atau penuh: tetap jalan dari memori', () => {
  const broken = { getItem: () => { throw new Error('SecurityError'); }, setItem: () => { throw new Error('QuotaExceeded'); } };
  const s = createStore(() => broken);
  s.recordResult('1-1', { stars: 2, blocks: 4, hints: 0, attempts: 1, points: 220 });
  assert.equal(s.progress('1-1').stars, 2);

  const none = createStore(() => null);
  none.recordResult('1-1', { stars: 2, blocks: 4, hints: 0, attempts: 1, points: 220 });
  assert.equal(none.isCompleted('1-1'), true);
});

test('reset menghapus progres tetapi tetap menyimpan data awal yang valid', () => {
  const storage = fakeStorage();
  const s = createStore(() => storage);
  s.recordResult('1-1', { stars: 3, blocks: 3, hints: 0, attempts: 1, points: 270 });
  s.reset();
  assert.deepEqual(s.state().progress, {});
  assert.equal(JSON.parse(storage.data[KEY]).points, 0);
});
