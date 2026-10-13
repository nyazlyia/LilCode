// resources/js/scoring.js
// Aturan bintang dan poin (lihat DOCS_ACUAN.md bagian 2.5).

/** 3 bintang: hemat dan tanpa petunjuk. 2 bintang: cukup hemat atau petunjuk <= 1. Selain itu 1 bintang. */
export function computeStars({ blocks, optimal, hints }) {
  if (blocks <= optimal && hints === 0) return 3;
  if (blocks <= optimal + 2 || hints <= 1) return 2;
  return 1;
}

/** 100 poin selesai + 50 per bintang + 20 bonus jika berhasil pada percobaan pertama. */
export function computePoints({ stars, attempts }) {
  return 100 + 50 * stars + (attempts === 1 ? 20 : 0);
}
