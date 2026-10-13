// resources/js/store.js
// Penyimpanan progres di localStorage (tanpa login). Aman jika localStorage tidak tersedia
// (mode privat, kuota penuh): otomatis memakai memori sementara.
const KEY = 'petualangan-kode:v1';
const VERSION = 1;

const newId = () =>
  globalThis.crypto?.randomUUID?.() ?? `p-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`;

const fresh = () => ({
  version: VERSION,
  player: { id: newId(), nickname: '', createdAt: new Date().toISOString() },
  progress: {},
  points: 0,
  badges: [],
  settings: { sound: true, reduceMotion: false },
});

export function createStore(getStorage = () => (typeof window !== 'undefined' ? window.localStorage : null)) {
  let cache = null;

  const read = () => {
    if (cache) return cache;
    try {
      const raw = getStorage()?.getItem(KEY);
      if (raw) {
        const data = JSON.parse(raw);
        if (data?.version === VERSION && data.progress && typeof data.progress === 'object') {
          cache = { ...fresh(), ...data };
          return cache;
        }
      }
    } catch {
      // data rusak atau storage ditolak: mulai dari awal
    }
    cache = fresh();
    return cache;
  };

  const write = () => {
    try {
      getStorage()?.setItem(KEY, JSON.stringify(cache));
      return true;
    } catch {
      return false; // tetap berjalan dari memori
    }
  };

  const sumPoints = (state) => Object.values(state.progress).reduce((n, p) => n + (p.points ?? 0), 0);

  return {
    state: () => read(),
    progress: (slug) => read().progress[slug] ?? null,
    isCompleted: (slug) => Boolean(read().progress[slug]),

    /** Level terbuka jika pertama, atau level sebelumnya (menurut urutan) sudah selesai. */
    isUnlocked(slug, order) {
      const i = order.indexOf(slug);
      return i <= 0 ? i === 0 : this.isCompleted(order[i - 1]);
    },

    totalStars: () => Object.values(read().progress).reduce((n, p) => n + (p.stars ?? 0), 0),

    /**
     * Catat hasil level. Rekor disimpan per komponen (bintang tertinggi, blok paling sedikit, dst.)
     * sehingga mengulang level tidak pernah menurunkan nilai.
     */
    recordResult(slug, { stars, blocks, hints, attempts, points }) {
      const state = read();
      const old = state.progress[slug] ?? null;
      const now = new Date().toISOString();

      const entry = {
        stars: Math.max(stars, old?.stars ?? 0),
        bestBlocks: Math.min(blocks, old?.bestBlocks ?? Infinity),
        hintsUsed: Math.min(hints, old?.hintsUsed ?? Infinity),
        attempts: Math.min(attempts, old?.attempts ?? Infinity),
        points: Math.max(points, old?.points ?? 0),
        completedAt: old?.completedAt ?? now,
        updatedAt: now,
      };
      const isNewBest = !old || stars > old.stars || points > old.points;

      state.progress[slug] = entry;
      state.points = sumPoints(state);
      write();
      return { entry, isNewBest, firstTime: !old };
    },

    reset() {
      cache = fresh();
      write();
    },
  };
}

export const store = createStore();
