<?php

return [
    /*
    | Sumber data level: "json" (database/data/levels.json) atau "database" (tabel levels + hints di TiDB).
    | Default "json" agar situs tetap berjalan walau database belum siap. Saat memakai "database",
    | kegagalan koneksi atau tabel kosong otomatis kembali ke JSON.
    */
    'levels_source' => env('LEVELS_SOURCE', 'json'),
];
