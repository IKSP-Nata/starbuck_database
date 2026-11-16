# PRD Tambahan: Dashboard Hero Interaktif (Starbuck POS)

> Untuk AI coding agent (Gemini di Antigravity). Dokumen ini **melengkapi dan menimpa sebagian** `PRD.md` dan `starbuck_pos_ui_ux_skill.md`, khusus untuk halaman `pages/dashboard.php`.
> Baca seluruhnya sebelum menulis kode. Kerjakan bertahap (lihat bagian 9). Jika ragu soal database atau struktur file, **tanyakan dulu, jangan menebak**.

---

## 0. Tujuan

Mengubah `pages/dashboard.php` menjadi halaman utama bergaya **hero interaktif** ala landing page kafe gelap (referensi: template "Glacé"):

- Satu produk besar di tengah (foto dari kolom `gambar`).
- Teks raksasa transparan di belakang produk (nama produk).
- Judul + harga + tombol **Pesan Sekarang** di kiri.
- Deretan thumbnail produk di kanan. **Saat thumbnail diklik, seluruh hero berganti dengan animasi** (foto, judul, harga, teks belakang, warna cahaya latar).
- Di bawah hero tetap ada statistik dan Transaksi Terbaru (sesuai desain lama).

Hero ini adalah **satu kartu gelap di dalam aplikasi yang tetap terang** (Warm Minimalist). Halaman lain tidak berubah gaya.

---

## 1. Koreksi Terhadap Dokumen Sebelumnya (Penting)

Database sudah berubah. Skema aktual (`starbuck.sql`) sekarang:

| Tabel | Kolom |
|---|---|
| `produk` | `id_produk`, `nama_produk`, `harga`, **`gambar`** (varchar 255, nama file) |
| `kasir` | `id_kasir`, `nama_kasir`, `no_telp`, `alamat`, `email` |
| `transaksi` | `id_transaksi`, `subtotal`, `pajak`, `total`, `tanggal`, `waktu` |
| `detail_transaksi` | `id_detail_transaksi`, `id_kasir`, `id_produk`, `id_nota`, `id_transaksi`, `kategori`, `kuantitas`, `kuantitas_harga`, `harga` |

Konsekuensinya:
1. **Foto produk BOLEH dipakai** (kolom `gambar` ada). Larangan foto di `PRD.md` bagian 1.3 dan 3.4 **dicabut** untuk dashboard hero, kartu menu di `transactions/add.php`, dan `products/list.php`. Avatar inisial hanya dipakai sebagai **fallback** bila `gambar` kosong atau file tidak ditemukan.
2. **Tabel `produk` TIDAK punya kolom `stok`.** Hapus semua tampilan "Stok" yang disebut di `PRD.md` dan skill (bagian Produk). Jangan menambah kolom stok.
3. **Tabel `produk` tidak punya `kategori` maupun `deskripsi`.** Jangan membuat filter kategori atau teks deskripsi. (`kategori` ada di `detail_transaksi`, bukan untuk katalog.)
4. Kolom tambahan di `detail_transaksi` (`id_kasir`, `id_nota`, `kategori`, `kuantitas_harga`) **jangan disentuh**. Jangan mengubah query insert transaksi yang sudah berjalan.
5. Aturan lain tetap: 1 menu per transaksi, pajak 11%, tanpa pengurangan stok, `transactions/list.php` read-only, zona waktu `Asia/Makassar`, `ROOTPATH`, `$koneksi`.

---

## 2. Aturan Keras

1. **Jangan ubah** nama variabel PHP, nama kolom, query SQL, atau file di `process/`.
2. **Jangan ubah database.** Tidak ada tabel/kolom baru.
3. Perubahan di luar `dashboard.php` dibatasi pada: `assets/css/` (file baru `dashboard-hero.css`), `assets/js/` (file baru `dashboard-hero.js`), dan **satu perubahan kecil** di `pages/transactions/add.php` (lihat bagian 6).
4. Jangan memasang framework/library baru kecuali **GSAP via CDN** (lihat bagian 5) dan font Google yang disebut di bagian 3.2.
5. Tidak ada emoji, tidak ada gradien ungu/biru, tidak ada neon.
6. Semua teks yang berasal dari database harus di-escape (`htmlspecialchars` di PHP, `json_encode` dengan `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT` untuk dikirim ke JS).

---

## 3. Sistem Desain Hero

### 3.1 Warna (khusus hero, di dalam kartu)
```css
.hero {
  --hero-bg: #1A1210;          /* cokelat hampir hitam */
  --hero-bg-2: #241915;
  --hero-text: #F4EDE6;
  --hero-muted: rgba(244, 237, 230, 0.62);
  --hero-ghost: rgba(244, 237, 230, 0.07);   /* teks raksasa di belakang */
  --hero-line: rgba(244, 237, 230, 0.16);
  --hero-accent: #C9A27A;       /* emas-karamel lembut, dipakai hemat */
  --hero-glow: 78, 52, 46;      /* RGB default; di-override JS per produk */
}
```
Di luar hero tetap memakai palet `--bg-canvas`, `--primary-coffee`, dst. dari `PRD.md`.

### 3.2 Tipografi
- Judul produk dan teks raksasa: **Cormorant Garamond** (500/600), serif elegan. Tagline kecil memakai italic.
- Teks lain (harga, tombol, label): **Plus Jakarta Sans** seperti halaman lain.
- Teks raksasa: `font-size: clamp(120px, 17vw, 260px)`, `line-height: .9`, `white-space: nowrap`, boleh terpotong di tepi kartu (efek referensi), `user-select: none`, `pointer-events: none`, `aria-hidden="true"`.
- Judul produk di kiri: `clamp(34px, 3.6vw, 56px)`. Pecah nama menjadi maksimal 2 baris (kata terakhir turun ke baris kedua bila nama > 2 kata, misalnya "Grande Caramel / Macchiato").
- Harga: `tabular-nums`, 600, format `Rp 123.765`.

### 3.3 Anti "AI slop" khusus hero
- Satu warna aksen (`--hero-accent`), dipakai hanya untuk border thumbnail aktif dan garis kecil.
- Tidak ada ikon di dalam hero.
- Bayangan dan cahaya halus (radial glow beropasitas rendah), bukan neon.
- Ruang kosong lega; jangan menumpuk elemen di sekitar foto produk.
- Teks berbahasa Indonesia, singkat dan natural.

---

## 4. Struktur dan Konten Hero

Kartu hero: radius `24px`, tinggi `min(640px, 78vh)`, `overflow: hidden`, `position: relative`, latar `--hero-bg` + radial glow berwarna produk.

### 4.1 Lapisan (dari belakang ke depan)
1. **Latar:** `--hero-bg` + `radial-gradient(60% 70% at 50% 55%, rgba(var(--hero-glow), .35), transparent 70%)`. Vignette gelap halus di tepi.
2. **Teks raksasa** (nama produk), diposisikan vertikal sekitar 25-35% dari atas, rata tengah, terpotong di kiri/kanan.
3. **Foto produk** di tengah (lihat 4.2).
4. **Kolom kiri** (vertikal tengah): label kecil `Menu Pilihan`, judul produk (2 baris), harga, tombol.
5. **Kolom kanan:** tagline serif italic `Pilih menu favoritmu`, teks kecil `Klik untuk melihat detail menu`, lalu **strip thumbnail**.

### 4.2 Foto produk (PENTING: foto milik pengguna, kemungkinan bukan PNG transparan)
Foto di database adalah file `.jpg` dengan latar bawaan. Agar tidak terlihat seperti foto tempelan:
- Tampilkan dengan `object-fit: contain`, tinggi sekitar 80% kartu, lebar maksimal 46% kartu.
- Beri `mask-image` untuk memudarkan tepi sehingga menyatu dengan latar gelap:
  `mask-image: radial-gradient(ellipse 62% 66% at 50% 50%, #000 58%, transparent 100%);` (sertakan prefix `-webkit-mask-image`).
- Tambahkan `border-radius: 28px` pada foto sebagai cadangan bila mask tidak didukung.
- Jangan menambah filter berat. Boleh `filter: saturate(1.05) contrast(1.03)`.
- Jika file PNG transparan, hasilnya otomatis lebih baik; tidak perlu kode khusus.
- **Fallback:** bila `gambar` NULL/kosong atau gagal dimuat (`onerror`), tampilkan kotak inisial (2 huruf pertama nama produk) berukuran besar dengan gaya sederhana, bukan ikon rusak.
- **Lokasi file gambar:** jangan berasumsi. Lakukan audit untuk mengetahui folder tempat `products/add.php` / `process/` menyimpan upload (nama file di database seperti `1794847825_kopi.jpg`), lalu gunakan path dan helper yang sama. Jangan memindahkan atau mengubah nama file.

### 4.3 Warna cahaya per produk (tanpa kolom baru)
Setelah gambar dimuat, JS menghitung **warna rata-rata dominan**:
1. Gambar ke `<canvas>` 24x24 px, `getImageData`, rata-rata piksel yang tidak terlalu gelap/terang.
2. Turunkan kecerahan (pastikan tetap gelap/hangat), set CSS variable `--hero-glow` (format `r, g, b`) dengan transisi halus.
3. Bungkus dalam `try/catch`; bila gagal (misalnya canvas ter-taint), pakai warna default `78, 52, 46`.
4. Hasil perhitungan di-cache per produk.

### 4.4 Strip thumbnail (kanan)
- Berisi **semua produk** (saat ini 7). Tampilan: baris horizontal yang bisa digulir (`overflow-x: auto`, `scroll-snap-type: x mandatory`, scrollbar disembunyikan), tiap thumbnail 72x96px, radius 16px, border `1px var(--hero-line)`, latar `rgba(255,255,255,.04)`.
- Thumbnail aktif: border `--hero-accent`, latar sedikit lebih terang, naik 4px. Thumbnail non-aktif: opasitas .75.
- Thumbnail aktif otomatis di-scroll ke tengah strip (`scrollIntoView({inline:'center', behavior:'smooth'})`).
- Elemen berupa `<button>` dengan `aria-label="Pilih {nama_produk}"` dan `aria-pressed`.
- Di bawah strip: indikator kecil `2 / 7` (nomor produk aktif), bukan titik-titik.

### 4.5 Tombol Pesan Sekarang
- Teks: **Pesan Sekarang**. Gaya: outline `--hero-text` 1px, radius 999px, tinggi 48px, padding 0 32px; hover isi terang (`--hero-text`) dengan teks gelap, transisi 200ms.
- `href` = `ROOTPATH` yang benar menuju `pages/transactions/add.php?id_produk={id}`. Link diperbarui oleh JS tiap produk berganti.

---

## 5. Perilaku dan Animasi

### 5.1 Arsitektur (kombinasi Vanilla JS + GSAP)
- **Vanilla JS** mengurus: state (index aktif), event klik/keyboard/swipe, preload gambar, update konten, perhitungan warna, aksesibilitas.
- **GSAP (CDN, versi terkunci, mis. `gsap@3.12.5` dari cdnjs/jsdelivr)** hanya mengurus **timeline animasi transisi**.
- **Wajib ada fallback:** jika `window.gsap` tidak tersedia (offline/CDN gagal) atau pengguna memilih `prefers-reduced-motion: reduce`, gunakan transisi CSS sederhana (fade 250ms) lewat class `.is-changing`. Dashboard harus tetap berfungsi penuh tanpa GSAP.
- Data produk dikirim dari PHP ke JS sebagai satu objek:
  ```html
  <script>window.HERO_PRODUCTS = <?= json_encode($heroProducts, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;</script>
  ```
  dengan tiap item: `{ id, nama, harga, hargaFormat, gambarUrl }`. Query: `SELECT id_produk, nama_produk, harga, gambar FROM produk ORDER BY id_produk ASC` memakai `$koneksi` yang sudah ada.
- Seluruh konten awal (produk pertama) **dirender oleh PHP** agar halaman tetap tampil bila JS gagal; JS hanya mengambil alih interaksi.

### 5.2 Transisi saat ganti produk (total sekitar 0,9 detik)
Urutan timeline (easing `power3.out`, in-out `power2.inOut`):
1. **Keluar (0,35s):** foto lama `opacity→0, y→+24, scale→.96`; teks raksasa lama `x→-60, opacity→0`; judul lama naik dan memudar per baris (`y→-16`, stagger .05); harga dan tombol memudar.
2. **Ganti konten** (nama, harga, `src` foto, teks raksasa, `href` tombol, `--hero-glow`) saat semua elemen sedang tidak terlihat.
3. **Masuk (0,55s):** foto baru `y: +36→0, scale: .94→1, opacity 0→1`; teks raksasa baru `x: +80→0`; judul masuk per baris dari bawah dengan mask (`overflow:hidden` pada pembungkus baris); harga dan tombol fade-in dengan stagger .06.
4. Cahaya latar bertransisi warna `600ms` (CSS transition pada variable lewat `@property` atau animasi GSAP pada nilai RGB).
- Klik saat transisi berjalan: **batalkan timeline lama** (`timeline.kill()`) dan langsung mulai transisi ke produk terbaru. Jangan menumpuk animasi.
- Mengklik produk yang sedang aktif: tidak melakukan apa pun.
- Gunakan `will-change: transform, opacity` hanya selama animasi; animasikan `transform`/`opacity` saja (hindari `width/height/top/left`).

### 5.3 Interaksi
- Klik thumbnail: ganti produk.
- Keyboard: `ArrowLeft` / `ArrowRight` pindah produk (saat fokus berada di dalam hero atau di strip), `Enter`/`Space` pada thumbnail memilih.
- Swipe kiri/kanan di area foto (touch) berpindah produk (ambang 50px).
- **Tanpa auto-rotate** (aplikasi kasir harus stabil dan tidak berubah sendiri).
- **Tanpa biji kopi melayang** (butuh aset tambahan; opsional untuk tahap lanjutan).
- Preload semua gambar produk saat halaman dimuat (idle) agar pergantian tidak berkedip.
- Parallax halus opsional (foto bergeser maksimal 8px mengikuti kursor), hanya di desktop dan dimatikan saat `prefers-reduced-motion`.

### 5.4 Aksesibilitas
- Area hero `aria-live="polite"` pada judul/harga agar perubahan terbaca screen reader.
- Fokus keyboard terlihat jelas di thumbnail dan tombol.
- Kontras teks pada latar gelap minimal 4.5:1.

---

## 6. Integrasi dengan `transactions/add.php` (satu-satunya perubahan di luar dashboard)

Tombol **Pesan Sekarang** mengirim `?id_produk=ID`. Di `add.php` tambahkan **JS kecil saja** (tanpa menyentuh PHP/backend/field form):
```js
const pid = new URLSearchParams(location.search).get('id_produk');
if (pid) {
  const card = document.querySelector('[data-id="' + CSS.escape(pid) + '"]');
  if (card) { card.click(); card.scrollIntoView({block: 'center', behavior: 'smooth'}); }
}
```
Syarat: kartu menu di `add.php` sudah punya atribut `data-id` (sesuai `PRD.md` 4.5). Jika belum, sesuaikan pemilih dengan struktur yang ada tanpa mengubah nama field form. Jika `id_produk` tidak valid, abaikan (halaman berjalan normal).

---

## 7. Bagian Bawah Dashboard (Tidak Berubah)

Di bawah hero, dengan jarak 32px:
- 4 kartu statistik (`row g-4`): Total Kasir, Total Produk, Total Transaksi, Total Pendapatan, gaya sesuai `PRD.md` 4.2 (putih, tanpa ikon, tautan `Lihat Data →`).
- Kartu **Transaksi Terbaru** + tombol `+ Transaksi Baru` dan `Lihat Semua`.
- Query statistik yang sudah ada **dipertahankan apa adanya**.

Sidebar 110px dan layout dari `PRD.md` 4.1 **tetap dipakai** (hero berada di area konten, bukan layar penuh).

---

## 8. Responsif

- **≥1200px:** layout 3 kolom seperti referensi (kiri teks, tengah foto, kanan strip).
- **768-1199px:** foto di tengah atas, teks di bawahnya, strip di bawah teks (gulir horizontal).
- **<768px:** susunan vertikal: foto, judul + harga, tombol, strip. Teks raksasa mengecil (`clamp`) dan opasitas diturunkan, tinggi kartu otomatis (`min-height`).
- Uji pada 375px, 768px, 1440px.

---

## 9. Urutan Eksekusi (Berhenti Per Tahap, Laporkan Dulu)

1. **Audit (tanpa mengubah file):** baca `config/config.php`, `include/header.php`, `include/footer.php`, `pages/dashboard.php`, `pages/transactions/add.php`, `pages/products/` dan `process/` terkait produk. Laporkan: (a) folder dan cara akses gambar produk, (b) query statistik dashboard saat ini, (c) apakah kartu menu di `add.php` punya `data-id`, (d) nama field form transaksi. Tunggu konfirmasi.
2. **Markup dan CSS statis:** hero di `dashboard.php` + `assets/css/dashboard-hero.css`, dirender PHP untuk produk pertama. Belum ada animasi.
3. **JS interaksi (tanpa GSAP):** `assets/js/dashboard-hero.js`: state, klik thumbnail, ganti konten, keyboard, swipe, preload, fallback gambar, tombol `href`.
4. **Animasi GSAP + fallback CSS + `prefers-reduced-motion`.**
5. **Warna cahaya dominan** (canvas) dan parallax opsional.
6. **Integrasi `add.php`** (bagian 6).
7. **Responsif dan QA** (checklist di bawah).

## 10. Kriteria Selesai

- [ ] Dashboard terbuka tanpa error PHP/SQL; semua tautan lama tetap berfungsi.
- [ ] Tidak ada perubahan database, nama kolom, query, atau file `process/`.
- [ ] Hero menampilkan produk dari tabel `produk` dengan foto dari kolom `gambar`; fallback inisial bekerja bila foto hilang.
- [ ] Klik thumbnail mengganti foto, judul, harga, teks raksasa, tombol, dan warna cahaya dengan animasi mulus (tanpa lompat/berkedip), dan klik beruntun tidak merusak animasi.
- [ ] Tanpa GSAP atau dengan `prefers-reduced-motion`, hero tetap berfungsi dengan fade sederhana.
- [ ] **Pesan Sekarang** membuka `add.php` dengan produk yang sesuai sudah terpilih di panel Bills.
- [ ] Nama panjang ("Grande Caramel Macchiato", "Tall Pike Place Roast") tidak merusak tata letak.
- [ ] Tidak ada tampilan "Stok", kategori, deskripsi, atau elemen tanpa kolom di database.
- [ ] Animasi hanya memakai `transform`/`opacity`; berjalan lancar di laptop biasa.
- [ ] Rapi di 375px, 768px, 1440px; kontras teks memadai.
- [ ] Bagian statistik dan Transaksi Terbaru di bawah hero tetap benar.

## 11. Catatan untuk Pemilik Proyek (bukan perintah untuk agent)

- Produk "Oat Milk" dan "Extra Shot" adalah tambahan (bukan minuman utama) tetapi akan ikut tampil di hero karena `produk` tidak punya kategori. Jika tidak diinginkan, putuskan nanti apakah dikecualikan lewat daftar ID tetap.
- Foto terbaik: PNG tanpa latar, sudut dan ukuran seragam. Foto `.jpg` tetap bisa dipakai berkat efek pudar di tepi (bagian 4.2).
