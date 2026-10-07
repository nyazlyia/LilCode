// resources/js/engine.js
// Mesin eksekusi blok Petualangan Kode. JS murni (tanpa DOM) agar mudah diuji.
//
// Kode petak grid: 0 jalan, 1 rintangan (batu), 2 air, 3 jembatan. Petak 1 dan 2 tidak bisa dilewati.
// Arah: 'N' atas, 'E' kanan, 'S' bawah, 'W' kiri. Koordinat (x, y) dengan y=0 di baris paling atas.
//
// Program = pohon blok:
//   { type: 'forward' } | { type: 'turn_left' } | { type: 'turn_right' }
//   { type: 'repeat', times: 2..9, body: [...] }
//   { type: 'if_obstacle', body: [...], else_body?: [...] }
// Tiap blok boleh punya `id` (dipakai UI untuk menyorot blok yang sedang jalan).
//
// Karakter berhenti begitu menginjak petak tujuan (status 'success').

export const MAX_STEPS = 500;   // pengaman langkah
export const MAX_DEPTH = 3;     // kedalaman maksimal blok bersarang
export const MIN_REPEAT = 2;
export const MAX_REPEAT = 9;

const DIRS = ['N', 'E', 'S', 'W'];
const VEC = { N: [0, -1], E: [1, 0], S: [0, 1], W: [-1, 0] };

/** Apakah petak (x, y) tidak bisa dilewati (rintangan, air, atau di luar peta)? */
export function isBlocked(level, x, y) {
  if (y < 0 || x < 0 || y >= level.grid.length || x >= level.grid[0].length) return true;
  const tile = level.grid[y][x];
  return tile === 1 || tile === 2;
}

/** Hitung semua blok (wadah dihitung 1, ditambah isinya). */
export function countBlocks(blocks) {
  let n = 0;
  for (const b of blocks) {
    n += 1;
    if (b.body) n += countBlocks(b.body);
    if (b.else_body) n += countBlocks(b.else_body);
  }
  return n;
}

/**
 * Validasi struktur program terhadap aturan level.
 * @returns {{ok: true} | {ok: false, status: 'invalid'|'too_many_blocks', reason: string}}
 */
export function validateProgram(level, program) {
  const allowed = new Set(level.allowed_blocks);

  const check = (blocks, depth) => {
    if (!Array.isArray(blocks)) return 'struktur program tidak valid';
    if (depth > MAX_DEPTH) return 'blok bersarang terlalu dalam';
    for (const b of blocks) {
      if (!b || typeof b !== 'object' || !allowed.has(b.type)) return `blok tidak diizinkan: ${b?.type}`;
      if (b.type === 'repeat') {
        if (!Number.isInteger(b.times) || b.times < MIN_REPEAT || b.times > MAX_REPEAT) return 'jumlah ulangan tidak valid';
        const err = check(b.body, depth + 1);
        if (err) return err;
      } else if (b.type === 'if_obstacle') {
        let err = check(b.body, depth + 1);
        if (err) return err;
        if (b.else_body !== undefined) {
          err = check(b.else_body, depth + 1);
          if (err) return err;
        }
      }
    }
    return null;
  };

  const error = check(program, 0);
  if (error) return { ok: false, status: 'invalid', reason: error };
  if (countBlocks(program) > level.max_blocks) {
    return { ok: false, status: 'too_many_blocks', reason: 'jumlah blok melebihi batas level' };
  }
  return { ok: true };
}

/**
 * Jalankan program pada level.
 * @returns {{
 *   status: 'success'|'crash'|'incomplete'|'too_long'|'too_many_blocks'|'invalid',
 *   steps: Array<{action:string, x:number, y:number, dir:string, blockId:(string|number|null), crashed?:boolean}>,
 *   final: {x:number, y:number, dir:string},
 *   blocksUsed: number,
 *   reason?: string
 * }}
 */
export function run(level, program) {
  const state = { x: level.start.x, y: level.start.y, dir: level.start.dir };
  const result = (status, steps, extra = {}) => ({
    status,
    steps,
    final: { x: state.x, y: state.y, dir: state.dir },
    blocksUsed: Array.isArray(program) ? countBlocks(program) : 0,
    ...extra,
  });

  const valid = validateProgram(level, program);
  if (!valid.ok) return result(valid.status, [], { reason: valid.reason });

  const steps = [];
  const ctx = { status: null, count: 0 };

  const record = (action, block, extra = {}) => {
    steps.push({ action, x: state.x, y: state.y, dir: state.dir, blockId: block.id ?? null, ...extra });
  };

  const tick = () => {
    ctx.count += 1;
    if (ctx.count > MAX_STEPS) {
      ctx.status = 'too_long';
      return false;
    }
    return true;
  };

  const exec = (blocks) => {
    for (const b of blocks) {
      if (ctx.status) return;
      switch (b.type) {
        case 'forward': {
          if (!tick()) return;
          const [dx, dy] = VEC[state.dir];
          const nx = state.x + dx;
          const ny = state.y + dy;
          if (isBlocked(level, nx, ny)) {
            record('forward', b, { crashed: true });
            ctx.status = 'crash';
            return;
          }
          state.x = nx;
          state.y = ny;
          record('forward', b);
          if (state.x === level.goal.x && state.y === level.goal.y) ctx.status = 'success';
          break;
        }
        case 'turn_left':
        case 'turn_right': {
          if (!tick()) return;
          const delta = b.type === 'turn_left' ? 3 : 1;
          state.dir = DIRS[(DIRS.indexOf(state.dir) + delta) % 4];
          record(b.type, b);
          break;
        }
        case 'repeat':
          for (let i = 0; i < b.times && !ctx.status; i++) exec(b.body);
          break;
        case 'if_obstacle': {
          const [dx, dy] = VEC[state.dir];
          const blocked = isBlocked(level, state.x + dx, state.y + dy);
          exec(blocked ? b.body : (b.else_body ?? []));
          break;
        }
      }
    }
  };

  exec(program);
  return result(ctx.status ?? 'incomplete', steps);
}
