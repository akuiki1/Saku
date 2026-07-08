# PRD — SAKU (Sistem Arsip Berkas Keuangan)

| | |
|---|---|
| **Versi** | 0.2 — diperbarui setelah analisis template Excel & arsip scan |
| **Tanggal** | 8 Juli 2026 |
| **Pemilik** | Staf keuangan Bidang Cipta Karya dan Penataan Ruang (CKPR), Dinas PUPR Kab. Hulu Sungai Tengah |
| **Status** | Menunggu review |
| **Stack** | Laravel 12 + Filament v5 + mPDF, MySQL/MariaDB (Laragon), PHP 8.3 — lihat [Desain Teknis](TECHNICAL-DESIGN.md) §1 |

---

## 1. Pernyataan Masalah

Pembuatan berkas keuangan bidang (kwitansi GU, dokumen pencairan LS) saat ini dilakukan manual lewat Excel. Setiap berkas rawan kesalahan — dan analisis file nyata membuktikannya:

- Workbook kwitansi makan minum berisi **53 sheet** tumpukan salinan lama; sheet aktif untuk rapat 7 Juli **2026** masih menulis "Tahun Anggaran **2025**" dan "Tanggal 7 Juli **2025**" sisa copy-paste.
- Kwitansi perjadin penuh error `#REF!` akibat baris terhapus, plus tanggal basi "Februari 2023".
- Kwitansi GU Dexlite yang **sudah distempel verifikasi dan diarsip** mencetak NIP orang lain di bawah nama PPTK (salah baris VLOOKUP).
- Master lookup berisi nomor urut dobel, kode sub kegiatan `?`, dan NIP yang beda antar baris untuk orang yang sama.
- Rumus menunjuk ke **file Excel lain** (`='[5]BAP '!M35`) yang putus kalau file dipindah; nilai jaminan pelaksanaan tertulis Rp 49.920.350 padahal rumus 5%-nya menghasilkan Rp 39.888.700.

Setelah berkas jadi, statusnya (sudah TTD siapa, sudah diverifikasi, sudah cair) tidak tercatat di mana pun. Arsip akhir di-scan lalu di-upload manual ke Google Drive dengan struktur folder yang dibuat tangan satu per satu.

## 2. Tujuan (Goals)

| # | Tujuan | Ukuran keberhasilan |
|---|--------|---------------------|
| G1 | Mempercepat pembuatan berkas | Waktu bikin 1 kwitansi GU dari ±15–30 menit (Excel) menjadi **< 5 menit** |
| G2 | Menghilangkan kesalahan klerikal | **0 kesalahan** terbilang, tahun anggaran, NIP/pejabat, dan kode rekening pada berkas tercetak — semua diambil dari master, tidak pernah diketik ulang |
| G3 | Kejelasan status setiap berkas | Posisi setiap berkas (tahap TTD/verifikasi/SIPD/cair/arsip) bisa dijawab **tanpa mengecek berkas fisik** |
| G4 | Arsip terstruktur dan cepat ditemukan | Menemukan file arsip apa pun **< 30 detik**; struktur folder Tahun → Sub Kegiatan → Triwulan → Kode Rekening terbentuk **otomatis** |
| G5 | Rekap tanpa kerja tambahan | Rekap belanja per sub kegiatan/triwulan/kode rekening tersedia otomatis |

## 3. Non-Goals (Di Luar Lingkup)

| # | Bukan tujuan | Alasan |
|---|--------------|--------|
| NG1 | Integrasi dengan SIPD | Tidak ada API publik. Input SIPD tetap manual; SAKU hanya mencatat statusnya |
| NG2 | Tanda tangan digital / e-sign | Alur TTD basah + meterai adalah proses kantor. SAKU hanya melacak progres |
| NG3 | Multi-bidang / seluruh dinas | Batasan proyek: satu bidang (CKPR), terutama satu pengguna |
| NG4 | Menerbitkan No. BKU | No. BKU (format `no urut/CK/bulan/tahun`, LS: `no urut/CK-LS/bulan/tahun`) adalah nomor register **satu dinas** yang diberikan ruang keuangan umum dan ditulis tangan di kwitansi. SAKU hanya **mencatat** nomor itu setelah registrasi; kwitansi dicetak dengan kolom No. BKU kosong |
| NG5 | Mengelola berkas LS yang jadi tanggung jawab PPTK | SAKU meng-generate 4 dokumen milik pengguna; berkas PPTK cukup ditandai "terkumpul" |
| NG6 | Aplikasi mobile | Web responsif cukup |
| NG7 | Membuat dokumen SIPD (SPP, SPM, SPTJM, SP2D, e-billing) | Dokumen-dokumen itu terbit dari SIPD/BPKAD/DJP; SAKU hanya menyimpan scan-nya di arsip |

## 4. Pengguna

**Persona utama (satu-satunya): Staf Keuangan Bidang CKPR.**
Membuat berkas GU dan LS, mengawal berkas keliling kantor untuk TTD dan verifikasi, menginput ke SIPD, transfer bank, membuat TBP, lalu men-scan dan mengarsipkan. Paham alur keuangan daerah, terbiasa Excel.

Catatan penting: **tidak semua kwitansi GU dibuat sendiri** — sebagian berkas GU diterima dalam kondisi "siap proses" (kwitansi dibuat pihak lain). Proses selanjutnya sama persis (TTD → verifikasi → registrasi → SIPD → transfer → TBP → scan → arsip); yang beda hanya titik masuknya.

Pengguna sekunder (P2): rekan satu bidang, read-only.

## 5. Alur Proses Saat Ini (As-Is)

### 5.1 GU (Ganti Uang)

1. Bikin kwitansi manual di Excel → cetak *(atau: terima berkas siap proses yang kwitansinya dibuat pihak lain — langkah selanjutnya sama)*
2. TTD Penerima → TTD PPTK → TTD Bendahara Pengeluaran Pembantu → TTD Kabid/KPA
3. Serahkan ke ruang keuangan umum → verifikasi berkas
4. Registrasi No. BKU manual di buku GU kantor (ditulis tangan di kwitansi)
5. Stempel "TELAH DIPERIKSA/DITELITI OLEH PPK SKPD" → paraf Sekretaris
6. Input ke SIPD → input NPD → transfer di bank (Bank Kalsel, dari rekening bidang) → bikin TBP di SIPD
7. Scan berkas → upload manual ke Google Drive: `Tahun / Sub Kegiatan / Triwulan / Kode Rekening Belanja / berkas`

**Isi berkas GU final (hasil scan):** kwitansi (+lampiran rincian sesuai jenis) + nota/bukti belanja + bukti transfer bank; semua distempel verifikasi.

### 5.2 LS (Langsung)

1. Pengguna membuat 4 dokumen dari satu sheet input: **Rincian Pembayaran, Berita Acara Pembayaran (BAP), Kwitansi, Resume Kontrak** (berkas lain diurus PPTK)
2. Berkas terkumpul → verifikasi resume kontrak oleh bagian pembangunan
3. Serahkan ke BPKAD → pencairan SP2D
4. Scan → upload ke Google Drive (struktur sama)

**Isi berkas LS final (hasil scan, ±18 halaman):** SP2D, SPM-LS, SPTJM, SPP, kwitansi bermeterai, e-billing pajak (PPN & PPh), surat keterangan pencatatan aset, BAP, Rincian Pembayaran, Resume Kontrak, jaminan-jaminan, dll.

## 6. Temuan Analisis Template (menjadi dasar desain)

Dari pembedahan file Excel nyata dan PDF arsip:

**T1 — Konteks organisasi.** Bidang CKPR punya ±13 sub kegiatan aktif dalam 6 program. Setiap sub kegiatan punya **PPTK tetap** (nama, NIP, no. rekening). Pejabat lain per bidang: KPA/Kabid (Elfha Yunia Rachman, 2026), Bendahara Pengeluaran Pembantu (Darmadi). Pejabat **berganti antar tahun** (KPA 2025 ≠ 2026) → dokumen wajib menyimpan *snapshot* pejabat saat dibuat, bukan referensi hidup.

**T2 — Kwitansi GU satu layout, beberapa varian isi.** Header sama (Tahun Anggaran, Program/Kegiatan/Sub Kegiatan/Rekening, No. BKU kosong), blok TTD sama (Yang Menerima; PPTK; Bendahara Pengeluaran Pembantu "Setuju dan lunas dibayar tanggal"; pernyataan "Barang/Pekerjaan telah diterima/diselesaikan"). Yang beda per jenis belanja:
- *Makan minum rapat*: rincian item (Makan 35 kotak × Rp40.000 × 1 kali, Snack) + blok pajak resto 7% + lampiran daftar tamu & nota.
- *Perjalanan dinas*: kwitansi kolektif (jumlah = total beberapa pegawai) + lampiran **Rincian Biaya Perjalanan Dinas per pegawai** (transport, penginapan × malam, uang harian × hari, tiket; "Perhitungan SPPD Rampung") + Daftar Pengeluaran Riil + ceklis SPD. Butuh **master pegawai** (±125 orang; nama, jabatan, NIP).
- *Pembelian sederhana / honor*: kwitansi tanpa blok rincian, lampiran nota.

**T3 — Pajak bervariasi.** GU: kadang tanpa pajak, kadang PPn + PPh 22 (1,5%) (tergantung ada yg 3,5% juga). LS konstruksi: PPN 11% inklusif (11/111 × nilai) + PPh final Ps. 4(2) 1,75% dari DPP; ID Billing DJP dicatat di SPM. Tarif **harus konfigurabel per dokumen** (jenis pajak & persentase), bukan hardcode.

**T4 — Data berkas LS** (field dari sheet `input`): rekanan (nama badan, direktur+jabatan, alamat, bank, no. rek, NPWP), kontrak (nomor, tanggal, tanggal selesai, nilai, jangka waktu, lokasi, konsultan pengawas), CCO & addendum 1–2, DPA (nomor, tanggal), kode rekening + uraian, jaminan uang muka & jaminan pelaksanaan (penjamin, nomor, nilai, tanggal, masa berlaku), skema pembayaran (uang muka %, termin-termin %, pembayaran s/d BAP yang lalu, potongan pengembalian uang muka), porsi sumber dana DAK/non-DAK, nomor SPP/SPM/SP2D.

**T5 — Terbilang dua macam.** (a) Nominal → teks rupiah ("dua ratus tiga puluh sembilan juta … rupiah"); (b) tanggal → kalimat legal BAP ("Pada hari ini, Senin Tanggal Lima Belas Bulan Juni Tahun Dua Ribu Dua Puluh Enam").

**T6 — Format cetak.** Kwitansi & BAP: kertas Legal/F4 portrait; Rincian Pembayaran: landscape. Layout harus meniru template yang ada (sudah diterima verifikator bertahun-tahun).

**T7 — Riwayat pembayaran kontrak.** Rincian Pembayaran LS memuat tabel rekapitulasi: nilai kontrak, pembayaran s/d BAP yang lalu, pembayaran BAP ini, total, sisa kontrak (masing-masing dipecah DPP/PPN/PPh). Berarti entitas kontrak LS menyimpan **beberapa pembayaran berurutan** (uang muka, termin I…, akhir) dan dokumen termin ke-N menghitung dari termin sebelumnya.

## 7. Gambaran Solusi

Aplikasi web lokal (Laragon) dengan 5 modul:

1. **Master Data** — tahun anggaran; program → kegiatan → sub kegiatan (dengan PPTK per sub kegiatan); kode rekening belanja; pegawai; rekanan; pejabat penandatangan per peran (KPA/Kabid, Bendahara Pengeluaran Pembantu, Sekretaris).
2. **Generator Dokumen** — kwitansi GU (varian per jenis belanja) dan paket LS (4 dokumen dari satu input); terbilang & snapshot pejabat otomatis; PDF Legal/F4 meniru layout kantor.
3. **Pelacak Status** — checklist tahapan baku per jenis berkas (GU/LS) + kolom tanggal & nomor (No. BKU, No. NPD, No. TBP, No. SPP/SPM/SP2D).
4. **Arsip Digital** — upload scan; path `{tahun}/{sub_kegiatan}/{triwulan}/{kode_rekening}/` otomatis; pencarian & filter.
5. **Dashboard & Rekap** — berkas berjalan + posisi terakhir; rekap belanja; daftar belum diarsipkan.

## 8. User Stories

Prioritas dari atas ke bawah.

**Pembuatan dokumen**
- Sebagai staf keuangan, saya ingin membuat kwitansi GU dengan memilih sub kegiatan (PPTK ikut otomatis), kode rekening, dan penerima dari master lalu mengisi uraian dan rincian item, sehingga kwitansi jadi < 5 menit tanpa salah ketik.
- Sebagai staf keuangan, saya ingin terbilang dihitung otomatis dari nominal, sehingga tidak ada selisih angka vs terbilang.
- Sebagai staf keuangan, saya ingin nama+NIP pejabat diambil dari master dan dibekukan di dokumen (snapshot), sehingga tidak ada lagi NIP orang lain tercetak di kwitansi.
- Sebagai staf keuangan, saya ingin mencetak PDF dengan layout sama seperti template Excel kantor (F4/Legal), sehingga diterima verifikator tanpa penyesuaian.
- Sebagai staf keuangan, saya ingin membuat satu paket LS (Rincian Pembayaran, BAP, Kwitansi, Resume Kontrak) dari satu form data kontrak, sehingga data tidak diketik 4 kali.
- Sebagai staf keuangan, saya ingin pembayaran termin LS menghitung otomatis dari riwayat termin sebelumnya (uang muka, potongan pengembalian, sisa kontrak), sehingga tabel rekapitulasi tidak dihitung manual.
- Sebagai staf keuangan, saya ingin menduplikasi berkas dari transaksi serupa (honor bulanan, makan minum rutin), sehingga tinggal ubah tanggal dan nominal — tanpa risiko data basi ikut terbawa (sistem mengosongkan field tanggal/nomor).
- Sebagai staf keuangan, saya ingin mendaftarkan berkas GU yang kwitansinya dibuat pihak lain (hanya isi metadata: sub kegiatan, kode rekening, penerima, nominal, uraian — tanpa generate kwitansi), sehingga berkas titipan tetap bisa dilacak status dan diarsipkan sama seperti berkas yang saya buat sendiri.

**Pelacakan status**
- Sebagai staf keuangan, saya ingin melihat daftar berkas berjalan beserta tahap terakhirnya ("menunggu TTD Kabid", "di BPKAD"), sehingga tahu mana yang harus dikejar.
- Sebagai staf keuangan, saya ingin mencatat No. BKU/No. SP2D dan tanggalnya pada tahap terkait, sehingga register tidak hanya di buku kantor.
- Sebagai staf keuangan, saya ingin melihat berkas yang sudah cair tapi belum discan/diarsipkan, sehingga tidak ada yang lolos dari arsip.

**Arsip**
- Sebagai staf keuangan, saya ingin meng-upload hasil scan dan sistem otomatis menaruhnya di struktur folder yang benar, sehingga tidak bikin folder manual.
- Sebagai staf keuangan, saya ingin mencari arsip berdasarkan uraian, penerima/rekanan, sub kegiatan, triwulan, kode rekening, atau No. BKU, sehingga berkas lama ketemu dalam hitungan detik.

**Rekap**
- Sebagai staf keuangan, saya ingin rekap total belanja per sub kegiatan dan triwulan, sehingga pertanyaan pimpinan terjawab tanpa Excel rekapan terpisah.

## 9. Kebutuhan

### P0 — Wajib (MVP)

**R1. Master data**
- CRUD: tahun anggaran; program/kegiatan/sub kegiatan (kode + nama + PPTK: pegawai, no. rekening); kode rekening belanja; pegawai (nama, NIP, jabatan); rekanan (badan, direktur, jabatan, alamat, bank, no. rek, NPWP); pejabat per peran (KPA, Bendahara Pembantu, Sekretaris) per tahun anggaran.
- Kriteria: dropdown hanya menampilkan data tahun anggaran aktif; dokumen menyimpan snapshot nama/NIP/jabatan saat dibuat; validasi NIP format baku; tidak boleh ada kode sub kegiatan/rekening duplikat.

**R2. Berkas GU** — dua titik masuk, satu proses lanjutan yang sama:

*(a) Buat kwitansi di SAKU (default).*
- Form: sub kegiatan (auto PPTK), kode rekening, penerima (pegawai/rekanan/teks bebas + no. rekening), uraian pembayaran, tanggal, rincian item opsional (uraian × volume × satuan × harga), blok pajak opsional (PPn nominal, PPh persen konfigurabel).
- Terbilang otomatis; tahun anggaran otomatis dari master aktif; kolom "Kuitansi No." dan "No. BKU" dicetak kosong (diisi tangan saat registrasi).
- Output PDF F4/Legal meniru template; varian blok: dengan/tanpa rincian pembayaran & pajak.

*(b) Daftar berkas titipan (kwitansi dibuat pihak lain).*
- Form ringkas metadata saja: sub kegiatan, kode rekening, penerima, nominal, uraian, tanggal. Tanpa generate kwitansi PDF; kwitansi eksternal diunggah sebagai lampiran/arsip saat sudah discan.
- Ditandai sumber = "titipan" agar bisa dibedakan di daftar & rekap.

- Kriteria: kedua titik masuk menghasilkan satu entitas Berkas GU yang identik untuk tracking (R4), arsip (R5), dan rekap (R7) — hanya berbeda ada/tidaknya kwitansi hasil generate. Given nominal 1.925.000 Then terbilang "(satu juta sembilan ratus dua puluh lima ribu rupiah)"; jumlah kwitansi = total rincian item bila rincian diisi (tidak bisa beda); tidak ada cara mengetik NIP manual di form (a).

**R3. Paket dokumen LS**
- Entitas Kontrak LS (field sesuai T4) + entitas Pembayaran (jenis: uang muka/termin/akhir, persentase, jaminan terkait) → generate 4 PDF: Rincian Pembayaran, BAP, Kwitansi, Resume Kontrak.
- Hitung otomatis: DPP = 100/111 × nilai, PPN = 11/111 × nilai, PPh = tarif% × DPP (tarif konfigurabel per kontrak, default 1,75% konstruksi); tabel rekapitulasi menarik riwayat pembayaran sebelumnya pada kontrak yang sama; terbilang tanggal gaya legal untuk BAP.
- Kriteria: mengubah data kontrak memperbarui keempat dokumen; membuat termin ke-2 otomatis mengisi "Pembayaran s/d BAP yang lalu" dari termin ke-1; pembulatan konsisten (ROUND 0 desimal) di semua dokumen.

**R4. Tracking status**
- Checklist tahapan baku: GU = draf → cetak → TTD penerima → TTD PPTK → TTD Bendahara → TTD Kabid → verifikasi keuangan umum → registrasi No. BKU → paraf Sekretaris → input SIPD/NPD → transfer bank → TBP → scan → arsip. LS = 4 dokumen jadi → berkas PPTK terkumpul → verifikasi bagian pembangunan → di BPKAD → SP2D terbit → scan → arsip.
- Berkas GU titipan (R2b) memakai checklist GU yang sama tetapi masuk mulai tahap "terima berkas siap proses" (tahap draf/cetak otomatis dilewati/tidak wajib).
- Tiap tahap: tanggal + catatan/nomor (No. BKU, No. NPD, No. TBP, No. SPP/SPM/SP2D, no. referensi transfer).
- Kriteria: daftar berkas menampilkan tahap terakhir; tahap boleh diisi tidak berurutan; berkas bisa ditandai "ditolak/dikembalikan" dengan alasan.

**R5. Arsip digital**
- Upload file scan (PDF/JPG) per berkas; path `{tahun}/{sub_kegiatan}/{triwulan}/{kode_rekening}/` otomatis; triwulan dihitung dari tanggal berkas.
- Pencarian kata kunci + filter tahun/sub kegiatan/triwulan/kode rekening/jenis (GU-LS)/status; file bisa diunduh/dilihat kembali.
- Kriteria: berkas tanpa scan muncul di daftar "belum diarsipkan"; nama file tersimpan mengikuti pola konsisten (mis. `GU {uraian singkat} {tanggal}.pdf`).

**R6. Autentikasi sederhana** — login satu akun.

### P1 — Sebaiknya Ada (fast follow)

- **R7. Dashboard rekap** — total belanja per sub kegiatan/triwulan/kode rekening; jumlah berkas per status.
- **R8. Duplikasi berkas** — dengan pengosongan otomatis field tanggal/nomor/No. BKU (anti data basi).
- **R9. Ekspor rekap Excel.**
- **R10. Register internal** — daftar berkas berurutan No. BKU per bulan, cermin digital buku GU kantor.
- **R11. Lampiran perjadin** — Rincian Biaya Perjalanan Dinas per pegawai (+ Perhitungan SPPD Rampung), Daftar Pengeluaran Riil, ceklis SPD; kwitansi kolektif menjumlah rincian per pegawai. *(Sebelum ini jadi, kwitansi perjadin tetap bisa dibuat sebagai kwitansi generik + lampiran manual.)*
- **R12. Sinkronisasi Google Drive** — upload otomatis arsip via Drive API dengan struktur sama. *(Sebelum jadi: folder lokal SAKU sudah terstruktur, tinggal drag & drop.)*

### P2 — Pertimbangan ke Depan

- **R13. Multi-user read-only** untuk rekan bidang.
- **R14. Jenis dokumen tambahan** (daftar tamu rapat, dokumen honor tenaga ahli, dsb.) — generator dibuat per-template sejak awal.
- **R15. Import master** dari DPA/Excel (termasuk import master pegawai 125 orang dari `Sheet1` file perjadin).
- **R16. Pengingat berkas mandek** terlalu lama di satu tahap.

## 10. Metrik Keberhasilan

**Indikator cepat (2 minggu pertama):**
- 100% kwitansi GU baru dibuat lewat SAKU; waktu pembuatan < 5 menit.
- 0 berkas ditolak verifikator karena kesalahan ketik/terbilang/NIP/nomor.

**Indikator jangka panjang (1 triwulan):**
- 100% berkas triwulan berjalan tercatat status + terarsip dengan scan.
- Pencarian arsip < 30 detik; rekap triwulan dari SAKU tanpa Excel terpisah.

**Cara ukur:** evaluasi mandiri akhir minggu ke-2 dan akhir triwulan pertama pemakaian.

## 11. Batasan Teknis & Asumsi

- **Hosting:** lokal via Laragon. Konsekuensi: backup DB + folder arsip wajib rutin (kehilangan PC = kehilangan arsip). Jadwal backup masuk desain teknis.
- **Framework/UI:** Laravel 12 + Filament v5 (Filament belum dukung Laravel 13 per Jul 2026; upgrade menyusul). **PDF:** mPDF (fallback Browsershot) — uji replikasi layout kwitansi F4 jadi gerbang Fase 1.
- **Terbilang:** helper sendiri + test (nominal → rupiah; tanggal → kalimat legal). `NumberFormatter` intl sebagai pembanding.
- **File arsip** di disk lokal (`storage/`), bukan di database. Ukuran scan bisa puluhan MB per berkas.
- **Asumsi:** struktur kode mengikuti DPA bidang (format Permendagri 90/2019); layout template 2026 yang dianalisis adalah layout yang berlaku.

## 12. Pertanyaan Terbuka

Q1 (template), Q2 (pajak), Q3 (penomoran) versi 0.1 **sudah terjawab** lewat analisis file — dirangkum di §6. Tersisa:

| # | Pertanyaan | Memblokir? |
|---|-----------|------------|
| Q4 | Upload ke Google Drive kantor: wajib aturan, atau boleh digantikan arsip lokal + backup? (menentukan prioritas R12) | Tidak — default: manual dulu |
| Q5 | Master sub kegiatan/kode rekening: input manual dari DPA, atau ada file DPA Excel untuk diimpor? | Tidak — default: input manual |
| Q6 | Jenis pajak GU selain PPn + PPh 22 1,5% yang pernah dipakai (mis. PPh 21 honor, PPh 23 jasa)? Sistem dibuat konfigurabel, tapi daftar default membantu | Tidak |
| Q7 | Perjadin: apakah tarif uang harian/penginapan per golongan perlu jadi master (standar biaya/SBU), atau cukup diketik per rincian? | Tidak — memengaruhi R11 (P1) |
| Q8 | Scan berkas: satu PDF gabungan per berkas (praktik sekarang) dipertahankan, atau ingin pecah per jenis dokumen? | Tidak — default: satu PDF gabungan |

## 13. Fase Pengembangan

| Fase | Isi | Hasil yang bisa dipakai |
|------|-----|-------------------------|
| **Fase 1 — Fondasi + GU** | Setup Laravel 12 + Filament v5, auth (R6), master data (R1), kwitansi GU + PDF (R2), arsip upload + pencarian (R5) | Kwitansi GU tidak lagi dibuat di Excel; arsip mulai terstruktur |
| **Fase 2 — Tracking + LS** | Checklist status (R4), paket 4 dokumen LS (R3), duplikasi (R8), lampiran perjadin (R11) | Seluruh alur GU & LS tercakup; status terpantau |
| **Fase 3 — Rekap + Integrasi** | Dashboard (R7), ekspor Excel (R9), register (R10), Google Drive (R12) | Rekap otomatis; upload Drive otomatis |

Tidak ada tenggat eksternal. Saran: selesaikan Fase 1 sampai dipakai harian, baru Fase 2 — umpan balik nyata mengoreksi asumsi PRD.

---

*Riwayat: v0.1 draf awal (8 Jul 2026). v0.2 — hasil analisis 10 file Excel template GU/LS dan PDF arsip scan; menjawab Q1–Q3; menambah §6 Temuan, master pegawai/rekanan, pajak konfigurabel, riwayat termin LS, No. BKU eksternal (NG4).*
