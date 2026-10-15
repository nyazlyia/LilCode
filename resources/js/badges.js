// resources/js/badges.js
// Definisi lencana. Status dihitung dari progres tersimpan (tidak perlu disimpan terpisah).

export const BADGES = [
  { id: 'first-step', name: 'Langkah Pertama', desc: 'Selesaikan level pertamamu.', icon: 'forward',
    test: (c) => c.done >= 1 },
  { id: 'world-1', name: 'Penjelajah Urutan', desc: 'Selesaikan semua level di Dunia 1.', icon: 'map',
    test: (c) => c.worldDone(1) },
  { id: 'world-2', name: 'Raja Pengulangan', desc: 'Selesaikan semua level di Dunia 2.', icon: 'repeat',
    test: (c) => c.worldDone(2) },
  { id: 'world-3', name: 'Detektif Kondisi', desc: 'Selesaikan semua level di Dunia 3.', icon: 'if_obstacle',
    test: (c) => c.worldDone(3) },
  { id: 'stars-10', name: 'Kolektor Bintang', desc: 'Kumpulkan 10 bintang.', icon: 'star',
    test: (c) => c.stars >= 10 },
  { id: 'perfect-5', name: 'Bintang Sempurna', desc: 'Raih 3 bintang di 5 level.', icon: 'star',
    test: (c) => c.perfect >= 5 },
  { id: 'independent', name: 'Mandiri', desc: 'Selesaikan 5 level tanpa petunjuk Pipo.', icon: 'forward',
    test: (c) => c.noHint >= 5 },
  { id: 'one-shot', name: 'Sekali Jalan', desc: 'Berhasil di percobaan pertama pada 3 level.', icon: 'play',
    test: (c) => c.oneShot >= 3 },
];

/**
 * @param {Record<string, {stars?: number, hintsUsed?: number, attempts?: number}>} progress
 * @param {Array<{world: number, levels: Array<{slug: string}>}>} worlds
 */
export function evaluateBadges(progress, worlds) {
  const entries = Object.values(progress);
  const count = (fn) => entries.filter(fn).length;

  const ctx = {
    done: entries.length,
    stars: entries.reduce((n, p) => n + (p.stars ?? 0), 0),
    perfect: count((p) => p.stars === 3),
    noHint: count((p) => p.hintsUsed === 0),
    oneShot: count((p) => p.attempts === 1),
    worldDone: (world) => {
      const levels = worlds.find((w) => w.world === world)?.levels ?? [];
      return levels.length > 0 && levels.every((l) => Boolean(progress[l.slug]));
    },
  };

  return BADGES.map(({ id, name, desc, icon, test }) => ({ id, name, desc, icon, earned: Boolean(test(ctx)) }));
}
