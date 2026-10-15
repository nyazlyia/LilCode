// Jalankan: node --test tests/js/badges.test.js
import test from 'node:test';
import assert from 'node:assert/strict';
import { BADGES, evaluateBadges } from '../../resources/js/badges.js';

const mk = (n, world) => Array.from({ length: n }, (_, i) => ({ slug: `${world}-${i + 1}` }));
const worlds = [
  { world: 1, levels: mk(5, 1) },
  { world: 2, levels: mk(5, 2) },
  { world: 3, levels: mk(5, 3) },
];
const earned = (progress) => evaluateBadges(progress, worlds).filter((b) => b.earned).map((b) => b.id);

test('tanpa progres: belum ada lencana', () => {
  assert.deepEqual(earned({}), []);
  assert.equal(evaluateBadges({}, worlds).length, BADGES.length);
});

test('level pertama selesai: Langkah Pertama', () => {
  assert.deepEqual(earned({ '1-1': { stars: 1, hintsUsed: 2, attempts: 4 } }), ['first-step']);
});

test('semua level satu dunia: lencana dunia itu saja', () => {
  const p = Object.fromEntries(mk(5, 2).map((l) => [l.slug, { stars: 1, hintsUsed: 3, attempts: 5 }]));
  const got = earned(p);
  assert.ok(got.includes('world-2'));
  assert.ok(!got.includes('world-1') && !got.includes('world-3'));
});

test('bintang, bintang sempurna, mandiri, dan sekali jalan', () => {
  const p = Object.fromEntries(mk(5, 1).map((l) => [l.slug, { stars: 3, hintsUsed: 0, attempts: 1 }]));
  const got = earned(p);
  for (const id of ['stars-10', 'perfect-5', 'independent', 'one-shot', 'world-1', 'first-step']) {
    assert.ok(got.includes(id), `harus mendapat ${id}`);
  }
});

test('dunia tanpa level tidak pernah memberi lencana dunia', () => {
  const none = evaluateBadges({ '1-1': { stars: 3 } }, [{ world: 3, levels: [] }]);
  assert.ok(!none.find((b) => b.id === 'world-3').earned);
});

test('data progres sebagian/rusak tidak menimbulkan error', () => {
  assert.doesNotThrow(() => evaluateBadges({ '1-1': {} }, worlds));
});
