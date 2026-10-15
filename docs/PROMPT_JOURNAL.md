# Jurnal Prompt (terkurasi, maksimal 5)

> **Aturan:** isi harus jujur sesuai yang benar-benar terjadi. Memalsukan atau memanipulasi dokumentasi prompt
> berakibat diskualifikasi. Log mentah awal-akhir disimpan terpisah di `docs/prompt-log/` (tidak dinilai,
> hanya untuk verifikasi keaslian).
>
> **Petunjuk mengisi:** di bawah ini sudah ada *kandidat* prompt dari percakapan yang memang terjadi.
> Pilih, salin teks aslinya, lalu isi bagian bertanda `ISI`. Hash commit diambil dari `git log --oneline`.
> Di bagian "Pemahaman masalah", tulis dengan kata-katamu sendiri apa yang kamu pahami dan putuskan.

---

## 1. Ide / PRD

- **Tujuan:** menyusun dokumen acuan (PRD dan spesifikasi teknis) sebagai pegangan agent selama lomba.
- **Prompt (kandidat):** "Bantu saya menyusun Dokumen PRD (Product Requirement Document) dan System Docs Acuan Teknis yang komprehensif dalam format Markdown. Dokumen ini akan menjadi acuan utama pengembangan aplikasi selama lomba." (lanjutkan dengan salinan lengkap prompt aslimu: konsep, decision stack, struktur dokumen)
- **Hasil:** `DOCS_ACUAN.md` (tujuan, fitur, UI/UX, arsitektur, aturan agent).
- **Keputusan yang kamu ambil:** ISI (mis. memilih tanpa login, `localStorage`, TiDB hanya data statis)
- **Commit terkait:** ISI

## 2. Debugging (Vercel: build mencari folder `dist`)

- **Konteks:** Laravel dideploy ke Vercel, tetapi preset otomatis terbaca Vite.
- **Prompt (kandidat):** `Error: No Output Directory named "dist" found after the Build completed. Configure the Output Directory in your Project Settings. Alternatively, configure vercel.json#outputDirectory.`
- **Pemahaman masalah:** ISI (jelaskan dengan kata-katamu: kenapa preset Vite salah untuk proyek PHP)
- **Perbaikan:** preset diganti ke **Other**; `vercel.json` diberi `"framework": null` dan `"outputDirectory": "public"`.
- **Commit terkait:** `1a5db2b`, `c8c222d` (lihat riwayat commit tanggal 6 Oktober)

## 3. Audit dan optimasi

- **Tujuan:** ISI (mis. audit rahasia di repo, SEO, performa, header keamanan)
- **Prompt (kandidat):** ISI (mis. permintaan memeriksa kerapian repo: "sejauh ini repository githubku begini, gimana aman ga?", atau prompt audit yang kamu tulis nanti)
- **Hasil dan perubahan:** ISI
- **Commit terkait:** ISI

## 4. Finishing

- **Tujuan:** ISI (mis. polishing akhir, uji di URL Vercel, perapian dokumentasi)
- **Prompt:** ISI
- **Hasil dan perubahan:** ISI
- **Commit terkait:** ISI

## 5. Bebas (prompt terbaikmu yang lain)

- **Kandidat A, identitas visual:** "buat agar visualnya lebih bagus dan ini warnanya E11A45, E3AFC9, EFECD7, EDC92C karakternya lebih bagus jangan emoji kayak gitu!"
- **Kandidat B, fitur inti:** "lanjut level 3 dan buat teman ai pipo"
- **Kandidat C, debugging Laravel:** pesan `SQLSTATE[HY000]: General error: 1 no such table: sessions` saat `php artisan serve` (penyebab: `SESSION_DRIVER=database` padahal migrasi bawaan dihapus; perbaikan: `SESSION_DRIVER=cookie`).
- **Pilihanmu:** ISI
- **Pemahaman dan keputusan:** ISI
- **Commit terkait:** ISI

---

## Cara menyimpan log mentah

Simpan seluruh percakapan (dari awal sampai akhir, tanpa disunting) sebagai berkas teks atau Markdown di
`docs/prompt-log/`, mis. `docs/prompt-log/claude-percakapan-lengkap.md`. Jika tidak ada fitur ekspor,
salin-tempel manual. Jangan menghapus bagian apa pun, termasuk percobaan yang gagal.
