// Komponen peta dunia dan tombol "Lanjutkan" di beranda.
import { store } from '../store.js';
import { evaluateBadges } from '../badges.js';

/** Peta: worlds = [{ world, name, blurb, color, levels: [{ slug, title, position, url }] }] */
export function worldMap(worlds) {
  const order = worlds.flatMap((w) => w.levels.map((l) => l.slug));
  const urls = Object.fromEntries(worlds.flatMap((w) => w.levels.map((l) => [l.slug, l.url])));

  return {
    worlds,
    get stars() {
      return store.totalStars();
    },
    get maxStars() {
      return order.length * 3;
    },
    get points() {
      return store.state().points;
    },
    get nextUrl() {
      const next = order.find((slug) => !store.isCompleted(slug));
      return next ? urls[next] : null;
    },
    get hasProgress() {
      return order.some((slug) => store.isCompleted(slug));
    },
    get allDone() {
      return order.length > 0 && this.nextUrl === null;
    },
    levelStars: (slug) => store.progress(slug)?.stars ?? 0,
    unlocked: (slug) => store.isUnlocked(slug, order),
    done: (slug) => store.isCompleted(slug),
    resetProgress() {
      if (window.confirm('Hapus semua progres dan mulai dari awal?')) {
        store.reset();
        window.location.reload();
      }
    },
  };
}

/** Beranda: levels = [{ slug, url }] */
export function resume(levels) {
  const order = levels.map((l) => l.slug);
  const urls = Object.fromEntries(levels.map((l) => [l.slug, l.url]));
  return {
    get hasProgress() {
      return order.some((slug) => store.isCompleted(slug));
    },
    get nextUrl() {
      const next = order.find((slug) => !store.isCompleted(slug));
      return next ? urls[next] : null;
    },
  };
}

/** Rak lencana: worlds = [{ world, levels: [{ slug }] }] */
export function badgeShelf(worlds) {
  return {
    get badges() {
      return evaluateBadges(store.state().progress, worlds);
    },
    get earned() {
      return this.badges.filter((b) => b.earned).length;
    },
    get stars() {
      return store.totalStars();
    },
    get points() {
      return store.state().points;
    },
  };
}
