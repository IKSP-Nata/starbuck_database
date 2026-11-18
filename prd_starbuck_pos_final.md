# PRD: Starbuck POS — Earthy Starbucks Operational POS

> **Instruksi untuk AI Coding Agent (Google Antigravity):**
> Dokumen ini adalah **satu-satunya sumber kebenaran utama (Single Source of Truth)** yang menggantikan seluruh versi PRD sebelumnya.
> Baca seluruh dokumen ini beserta `GEMINI.md` sebelum menulis atau mengubah kode. Kerjakan secara bertahap sesuai **Bagian 10**, dan **wajib berhenti serta melaporkan hasil di akhir setiap tahap** untuk menunggu konfirmasi pengguna. Jika ada hal yang meragukan terkait database atau struktur file, **tanyakan terlebih dahulu, jangan menebak**.

---

## 0. Konteks & Ketentuan Wajib Proyek

- **Proyek:** Aplikasi web kasir (*Point of Sale*) kafe **"Starbuck"** untuk tugas sekolah menggunakan PHP Native (dengan arsitektur PHP OOP) + MySQLi + Bootstrap 5.3 dan database MySQL `starbuck`.
- **Ketentuan Tugas yang Wajib Dipenuhi:**
  1. Tabel `kasir` pada kolom `id_kasir` wajib **AUTO_INCREMENT** (input ID Kasir manual pada form dihapus).
  2. Desain antarmuka (CSS & Vanilla JavaScript) dan arsitektur kode (**PHP OOP**) dirancang rapi, modern, dan fungsional.
  3. Setiap elemen UI yang tampil di layar **wajib** memiliki kolom di database dan logika pemrosesan di backend. **Dilarang keras membuat fitur atau tombol palsu (*dummy*)**.
  4. Seluruh pencatatan transaksi, tampilan jam, grafik harian, dan filter periode laporan wajib menggunakan zona waktu **WITA (`Asia/Makassar`)**.

---

## 1. Skema Database Aktual (`starbuck.sql`)

| Nama Tabel | Kolom |
|---|---|
| `produk` | `id_produk`, `nama_produk`, `harga` (decimal 12,0), `kategori` (varchar 50), `gambar` (varchar 255) |
| `kasir` | `id_kasir` (INT AUTO_INCREMENT), `nama_kasir`, `no_telp`, `alamat`, `email` |
| `transaksi` | `id_transaksi`, `subtotal`, `pajak`, `total`, `tanggal` (date), `waktu` (time) |
| `detail_transaksi` | `id_detail_transaksi`, `id_kasir`, `id_produk`, `id_nota`, `id_transaksi`, `kategori`, `kuantitas`, `kuantitas_harga`, `catatan` (varchar 100), `harga` |

**Catatan Data Eksisting:**
- Transaksi lama (`id_transaksi` 1 dan 2) memiliki beberapa baris `detail_transaksi`, sedangkan transaksi baru memiliki 1 baris per transaksi. Kueri riwayat dan struk harus tetap kompatibel menampilkan data lama maupun data baru tanpa *error* atau duplikasi baris.
- Tabel `produk` **tidak memiliki kolom `stok`**. Hapus semua tampilan atau logika stok.

---

## 2. Migrasi Database Wajib (Dijalankan di phpMyAdmin)

### 2.1 Perintah SQL Wajib
```sql
-- 1) Pastikan id_kasir AUTO_INCREMENT (ketentuan wajib tugas)
ALTER TABLE kasir MODIFY id_kasir INT NOT NULL AUTO_INCREMENT;

-- 2) Tambahkan kolom kategori pada tabel produk
ALTER TABLE produk
  ADD COLUMN kategori VARCHAR(50) NOT NULL DEFAULT 'Minuman' AFTER harga;

-- Set kategori produk sesuai jenis menu:
-- Oat Milk (2) dan Extra Shot (4) adalah Tambahan (Add-on), selebihnya Minuman
UPDATE produk SET kategori = 'Tambahan' WHERE id_produk IN (2, 4);
UPDATE produk SET kategori = 'Minuman' WHERE id_produk NOT IN (2, 4);

-- 3) Tambahkan kolom catatan kustomisasi pesanan pada detail_transaksi
ALTER TABLE detail_transaksi
  ADD COLUMN catatan VARCHAR(100) DEFAULT NULL AFTER kuantitas_harga;
```
Nilai kategori yang diizinkan di dalam sistem (dikunci sebagai konstanta kelas): `Minuman`, `Tambahan`, `Makanan`.

### 2.2 Aturan Pengisian `detail_transaksi` pada Transaksi Baru
Setiap transaksi baru wajib mengisi kolom berikut secara utuh (tanpa merusak data transaksi lama):
- `id_kasir`: diambil dari pilihan dropdown **Kasir Bertugas** pada panel *Bills* (wajib dipilih).
- `kategori`: disalin otomatis dari nilai `produk.kategori` menu yang dibeli.
- `kuantitas_harga`: hasil perkalian `harga * kuantitas`.
- `catatan`: teks gabungan pilihan **Suhu & Gula** (contoh: `Ice, Less sugar` atau `Hot, No sugar`) **hanya untuk produk berkategori `Minuman`**. Untuk produk berkategori `Tambahan` atau `Makanan`, wajib disimpan sebagai `NULL`.
- `id_nota`: pertahankan perilaku pengisian kode yang sudah berjalan berdasarkan hasil audit Tahap 0.

---

## 3. Aturan Keras (Hard Rules)

1. **Logika Perhitungan Transaksi:**
   - 1 transaksi = 1 jenis menu dengan jumlah beli (`kuantitas >= 1`).
   - Rumus: `subtotal = harga * kuantitas`, `pajak = round(subtotal * 0.11)` (pajak dikunci 11%), dan `total = subtotal + pajak`.
   - Dihitung secara *real-time* di JavaScript untuk pratinjau kasir, dan **wajib dihitung ulang di server (PHP)** saat penyimpanan. Pilihan suhu dan gula tidak mengubah harga produk.
2. **Larangan Fitur Dummy:**
   - Dilarang menampilkan stok produk, ukuran gelas (*Size*), metode pembayaran (*Payment Method*), diskon/promo, maupun **kalkulator uang tunai/kembalian** di panel *Bills*.
3. **Riwayat Transaksi Bersifat Read-Only & Berfilter Periode:**
   - Halaman `pages/transactions/list.php` tidak boleh memiliki tombol Edit atau Hapus.
   - Wajib memiliki filter periode tanggal dan bulan (default menampilkan transaksi **bulan berjalan** sesuai WITA).
4. **Kepatuhan Struktur Direktori & URL:**
   - Pertahankan struktur folder `config/`, `include/`, `pages/`, `process/`, dan `assets/`.
   - Folder baru yang diizinkan hanya `app/` untuk kelas-kelas PHP OOP (Bagian 8).
   - Semua atribut `action` pada form dan `href` navigasi wajib tetap mengarah ke path file yang sama.
   - Pertahankan `ROOTPATH`, variabel `$koneksi`, dan `date_default_timezone_set('Asia/Makassar');` beserta `SET time_zone = '+08:00'` pada koneksi MySQL.
5. **Keamanan & Sanitasi:**
   - Seluruh output dari database wajib di-*escape* menggunakan `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
   - Seluruh kueri SQL yang menerima input pengguna wajib menggunakan **Prepared Statements**.

---

## 4. Sistem Desain: Earthy Starbucks POS & Anti-AI Slop

### 4.1 Palet Warna (`assets/css/style.css`)
Menggunakan warna hangat (*earthy*) dan hijau khas kedai kopi Starbucks yang nyaman di mata kasir serta menyatu dengan logo Starbucks di sidebar:

```css
:root {
  --bg-canvas: #F7F5F0;           /* Warm Alabaster - latar belakang utama aplikasi */
  --bg-surface: #FFFFFF;          /* Pure White - permukaan kartu, tabel, dan panel Bills */
  --bg-subtle: #EFECE6;           /* Soft Cream - latar header tabel, avatar, & chip non-aktif */
  --primary-brand: #1E3932;       /* Starbucks House Green - tombol aksi utama & kartu sorotan */
  --primary-hover: #006241;       /* Starbucks Forest Green - state hover tombol utama */
  --accent-active: #00754A;       /* Accent Green - kotak aktif sidebar & chip pesanan terpilih */
  --accent-soft: #D4E9E2;         /* Soft Mint - badge kategori & aksen teks di atas kartu gelap */
  --text-main: #2C2A29;           /* Espresso Charcoal - judul, nama menu, harga, dan total */
  --text-muted: #6B6661;          /* Muted Roast - teks sekunder, tanggal/waktu, dan label */
  --border-soft: #E5E0D8;         /* Soft Sand - garis pemisah 1px solid antar komponen */
  --danger-soft-bg: #FDECE9;      /* Latar lembut tombol hapus & pesan peringatan */
  --danger-terracotta: #C84B31;   /* Terracotta Warm Red - teks/border tombol hapus */
  --warning-amber: #D48C2A;       /* Caramel Amber - indikator peringatan */
  --radius-card: 16px;
  --radius-control: 12px;
}
```

### 4.2 Sistem Duet Tipografi (`Plus Jakarta Sans` + `Inter`)
Muat kedua font dari Google Fonts di `include/header.php` dengan pembagian peran yang tegas:
1. **`Plus Jakarta Sans` (weights: 600, 700):**
   - Dipakai khusus untuk **Judul Halaman (`h1`–`h3`)**, **Judul Kartu/Panel (`Bills`, `Transaksi Terbaru`, `Menu Terlaris`)**, **Nama Produk pada Kartu Katalog**, **Label Navigasi Sidebar**, dan **Teks Tombol**.
   - Atur `letter-spacing: -0.02em` pada heading untuk memberi karakter modern dan hangat.
2. **`Inter` (weights: 400, 500, 600) dengan `font-variant-numeric: tabular-nums`:**
   - Dipakai khusus untuk **Seluruh Nominal Harga (`Rp ...`)**, **Angka Statistik Dashboard**, **Seluruh Isi Tabel & Tanggal/Waktu (WITA)**, **Input Form/Dropdown**, serta **Halaman Cetak Struk & Laporan**.
   - Memastikan angka `0`, `1`, dan deretan nominal harga sejajar lurus secara vertikal dan mudah dipindai kasir.

### 4.3 Disiplin Anti-AI Slop (Quiet Luxury UI)
- **Zero Box-in-Box Nesting:** Dilarang menaruh kartu bergaris tepi (`border`) di dalam container bergaris tepi lainnya. Gunakan ruang kosong vertikal (`16px–24px`), latar `--bg-subtle` (`#EFECE6`), atau garis pemisah tipis `1px solid var(--border-soft)`.
- **Permukaan Datar & Tegas:** Gunakan kartu `#FFFFFF` di atas latar `#F7F5F0` dengan `border: 1px solid var(--border-soft)` tanpa `box-shadow` tebal yang mengambang.
- **Tanpa Dekorasi Kosong:** Dilarang menggunakan emoji, gradien neon, ikon warna-warni di dalam kartu statistik, titik dua pada label kartu (`Total:`), maupun `form-floating`. Semua input form wajib menempatkan `<label class="form-label fw-medium">` di atas `<input>` atau `<select>`.

### 4.4 Animasi Fungsional (Murni CSS & Vanilla JS)
Hanya menganimasikan properti `transform` dan `opacity`, serta wajib mematikan animasi gerak saat `@media (prefers-reduced-motion: reduce)` aktif:
1. **Indikator Kotak Aktif Sidebar:** Kotak membulat `12px` berwarna `var(--accent-active)` (`#00754A`) di dalam sidebar bergeser halus secara vertikal (`300ms cubic-bezier(0.22, 1, 0.36, 1)`) saat berpindah menu.
2. **Count-Up Statistik & Grafik Batang:** Angka statistik di Dashboard menghitung naik selama `650ms`, dan batang grafik pendapatan 7 hari tumbuh halus dari bawah (`450ms`).
3. **Interaksi POS (`add.php`):** Kartu menu yang dipilih bertransisi halus (`150ms`), dan bagian pilihan **Suhu & Gula** membuka/menutup (*collapse*) secara mulus sesuai kategori produk.
4. **Toast & Modal Konfirmasi:** Gunakan *Toast* di kanan atas (hilang otomatis dalam 3 detik) untuk pesan sukses/gagal (menggantikan `alert()`), dan gunakan *Modal Bootstrap* untuk konfirmasi hapus data (menggantikan `confirm()`).

---

## 5. Spesifikasi Detail Per Halaman

### 5.1 Layout & Navigasi Sidebar (`include/header.php`, `include/footer.php`)
- **Sidebar Desktop (`>= 992px`):** Posisi `fixed` di kiri, lebar `110px`, latar `#FFFFFF`, `border-right: 1px solid var(--border-soft)`.
- **Logo Brand:** Bagian paling atas sidebar menampilkan logo Starbucks (`assets/img/logo-starbuck.png`, ukuran `48x48px`, `border-radius: 50%`) yang mengarah ke `dashboard.php`, dengan fallback teks `Starbuck` jika gambar gagal dimuat.
- **Item Navigasi & Indikator Aktif (Curved Protruding Circle Indicator + Idle Animation):**
  - 4 menu vertikal: **Dashboard**, **Kasir**, **Produk**, **Transaksi**. Untuk item non-aktif, ikon outline (`20px`) tampil tepat di atas label teks (`12px`) secara rata tengah.
  - **Indikator Aktif Menonjol dengan Lekukan Halus:** Menggunakan elemen `.nav-curve-indicator` berbentuk lingkaran (`56px`, warna `var(--accent-active)` `#00754A`, bingkai `#F7F5F0`) yang menonjol keluar di tepi kanan sidebar dengan lekukan halus (*curved cutout*) atas dan bawah menyatu dengan latar `#F7F5F0`.
  - Saat menu berstatus `.active`, ikon menu berpindah masuk ke tengah lingkaran `.nav-curve-indicator` berwarna putih `#FFFFFF`, sementara teks label menu tampil di sisi kiri dalam sidebar dengan `padding-right` yang cukup agar tidak bertabrakan dengan lingkaran.
  - Menggunakan JavaScript (`sessionStorage`) agar indikator meluncur vertikal halus (`transform 400ms cubic-bezier(0.22, 1, 0.36, 1)`), diikuti *idle animation* bernapas/mengambang lembut (`@keyframes` `3s ease-in-out infinite`) pada lingkaran aktif dan ikon di dalamnya setelah transisi selesai (nonaktif otomatis pada `prefers-reduced-motion: reduce`).
- **Layar Mobile (`< 992px`):** Sidebar berubah menjadi navbar atas yang rapi dengan tombol *collapse* Bootstrap.

### 5.2 Dashboard Operasional (`pages/dashboard.php`)
Menampilkan ringkasan penjualan kasir secara langsung tanpa banner *landing page*:
- **Header:** Judul **Dashboard** di kiri bersama subjudul tanggal hari ini dalam format Indonesia (WITA), serta tombol outline `Cetak Laporan` dan tombol utama `+ Transaksi Baru` (`--primary-brand`) di kanan.
- **4 Kartu Statistik (1 Baris Sejajar `row g-3`, `col-sm-6 col-xl-3`):**
  1. **Total Kasir** (jumlah data di `kasir` + tautan `Lihat Data →`).
  2. **Total Produk** (jumlah data di `produk` + tautan `Lihat Data →`).
  3. **Total Transaksi** (jumlah data di `transaksi` + tautan `Lihat Data →`).
  4. **Total Pendapatan (Focal Card):** Akumulasi `SUM(total)` dari `transaksi`. Khusus kartu ke-4 ini diberi latar **`var(--primary-brand)` (`#1E3932`)**, label atas berwarna `--accent-soft` (`#D4E9E2`), angka nominal berwarna putih `#FFFFFF`, dan tautan `Lihat Data →` berwarna `--accent-soft`.
- **Baris Tengah (`row g-4 mt-1`):**
  - **Kolom Kiri (`col-lg-7`) — Pendapatan 7 Hari Terakhir (WITA):** Grafik batang vertikal murni HTML/CSS (tanpa library eksternal) yang menampilkan total penjualan per hari selama 7 hari terakhir (`date('Y-m-d', strtotime('-6 days'))` s/d hari ini). Hari tanpa transaksi tetap ditampilkan dengan nilai `Rp 0`.
  - **Kolom Kanan (`col-lg-5`) — Menu Terlaris:** Menampilkan 5 produk dengan `SUM(kuantitas)` tertinggi dari `detail_transaksi JOIN produk`. Setiap baris memuat foto kecil produk (`40x40px` atau inisial 2 huruf), nama menu, jumlah terjual, dan garis bar horizontal proporsional.
- **Baris Bawah:** Kartu **Transaksi Terbaru** (5 transaksi terakhir lengkap dengan Tanggal, Waktu WITA, Menu, Total, dan tombol `Cetak Struk`) beserta tombol `Lihat Semua`.

### 5.3 Manajemen Kasir (`pages/cashiers/`)
- **`list.php`:** Header judul + pencarian nama kasir (live filter JS) + tombol `+ Tambah Kasir`. Tabel bersih di dalam kartu putih dengan tombol `Edit` dan `Hapus` (membuka modal konfirmasi Bootstrap).
- **`add.php` & `edit.php`:** Kartu form terpusat (`max-width: 560px`). **Hapus input manual "ID Kasir" di `add.php`** karena `id_kasir` menggunakan `AUTO_INCREMENT`. Input yang ditampilkan: Nama Kasir, No. Telepon, Alamat, dan Email.
- **Penanganan Foreign Key:** Jika pengguna menghapus kasir yang sudah tercatat di `detail_transaksi`, tangkap pengecualian/cek keterkaitan terlebih dahulu dan tampilkan notifikasi *Toast* yang jelas (*"Kasir ini sudah tercatat pada riwayat transaksi dan tidak dapat dihapus"*), bukan pesan *fatal error* SQL.

### 5.4 Manajemen Produk (`pages/products/`)
- **`list.php`:** Header berisi judul, **Pencarian Menu** (live filter JS), dan tombol `+ Tambah Produk`. Katalog ditampilkan dalam bentuk **Card Grid** (`row g-4`, `col-md-6 col-xl-4`): menampilkan foto produk (`gambar` dari folder `assets/img/products/` dengan bingkai membulat halus, atau fallback avatar 2 huruf inisial berlatar `--accent-soft`), badge kecil `kategori` (`Minuman`, `Tambahan`, `Makanan`), Nama Produk (`Plus Jakarta Sans`), Harga (`Inter tabular-nums`), serta tombol `Edit` dan `Hapus` (dengan modal konfirmasi & proteksi *foreign key*). **Tanpa tampilan stok.**
- **`add.php` & `edit.php`:** Kartu form (`max-width: 560px`) berisi field: Nama Produk, Harga, **Kategori** (`<select name="kategori" class="form-select">` berisi `Minuman`, `Tambahan`, `Makanan`), dan Upload Gambar (lengkap dengan pratinjau gambar saat ini pada halaman edit).

### 5.5 Halaman Transaksi Baru (`pages/transactions/add.php`)
Menggunakan tata letak *split-screen* kasir: `col-lg-7` (Katalog Pilih Menu di kiri) dan `col-lg-5` (Panel **Bills** *sticky* di kanan).

**A. Kolom Kiri — Katalog Pilih Menu:**
- Menampilkan jumlah menu tersedia, kotak pencarian *live filter*, dan deretan kartu produk.
- Setiap kartu produk menyimpan atribut `data-id`, `data-nama`, `data-harga`, `data-kategori`, dan `data-gambar`.
- Mendukung prapilih otomatis apabila halaman dibuka dengan parameter URL `?id_produk=ID`.

**B. Kolom Kanan — Panel Bills (Sticky):**
1. **Header Bills:** Judul `Bills` dan subjudul `Ringkasan Transaksi`.
2. **Kasir Bertugas (Wajib):** `<label class="form-label fw-medium">Kasir Bertugas</label>` diikuti `<select name="id_kasir" id="id_kasir" class="form-select" required>` yang memuat seluruh daftar kasir dari tabel `kasir`. Simpan pilihan kasir terakhir di `localStorage` (di dalam `try/catch`) agar kasir tidak perlu memilih ulang setiap transaksi.
3. **Item Terpilih & Kuantitas:** Menampilkan foto thumbnail/inisial, nama produk, harga satuan, serta tombol pengatur kuantitas (`-` / angka / `+`, minimal 1). Jika belum ada produk dipilih, tampilkan pesan status kosong yang rapi.
4. **Pilihan Kustomisasi Minuman (Kondisional — Hanya untuk `kategori === 'Minuman'`):**
   - Bagian ini **hanya tampil** (membuka halus) apabila produk yang dipilih memiliki `data-kategori="Minuman"`.
   - **Pilihan Suhu (Penyajian):** Tombol chip `<button type="button">` dengan opsi **`Ice`** dan **`Hot`** (default: **`Ice`**).
   - **Pilihan Gula (Sugar Level):** Tombol chip `<button type="button">` dengan opsi **`No sugar`**, **`Less sugar`**, **`Normal`**, dan **`High sugar`**.
   - **Penyesuaian Default Cerdas Berdasarkan Nama Minuman:**
     - Jika nama minuman mengandung kata **`Americano`**, **`Pike Place`**, atau **`Espresso`** (kopi hitam tanpa susu), otomatis atur pilihan default gula ke **`No sugar`** saat produk diklik (kasir tetap dapat mengklik chip gula lain jika pembeli meminta gula).
     - Untuk minuman lainnya (seperti *Mocha Latte*, *Caramel Macchiato*, *Matcha Latte*), otomatis atur pilihan default gula ke **`Normal`**.
   - **Untuk Kategori `Tambahan` (`Oat Milk`, `Extra Shot`) atau `Makanan`:** Sembunyikan bagian Suhu & Gula secara otomatis dan pastikan input `catatan` bernilai kosong (`NULL`).
5. **Rincian Pembayaran:** Menampilkan `Subtotal`, `Pajak (11%)`, garis pemisah putus-putus `1px dashed var(--border-soft)`, dan **`Total`** (`24px`, `Inter` bold `tabular-nums`).
6. **Tombol Aksi:** Tombol penuh **Simpan & Cetak Struk** (nonaktif sampai produk dan kasir dipilih) dan tombol outline **Kembali ke Riwayat**. Tanpa input uang bayar/kembalian.

### 5.6 Riwayat Transaksi, Struk 80mm, & Laporan (`pages/transactions/`)
- **`list.php` (Filter Periode Wajib Sesuai Zona Waktu WITA):**
  - Saat dibuka tanpa parameter filter, tabel **wajib hanya menampilkan transaksi pada bulan berjalan** (`DATE_FORMAT(t.tanggal, '%Y-%m') = date('Y-m')` berdasarkan `Asia/Makassar`), bukan menampilkan seluruh transaksi sekaligus.
  - Sediakan bar filter rapi di atas tabel (form `GET`, label di atas input) yang mencakup:
    1. **Dari Tanggal** (`tanggal_awal`) & **Sampai Tanggal** (`tanggal_akhir`).
    2. **Pilih Bulan** (`bulan`, format `YYYY-MM`).
    3. Tombol **Terapkan**, serta tombol cepat **Hari Ini**, **Bulan Ini**, dan **Semua**.
  - Tampilkan subjudul periode aktif (contoh: `Periode: November 2026` atau `18 Nov 2026`).
  - Kolom tabel: `No`, `Tanggal`, `Waktu (WITA)`, `Kasir` (tampilkan `-` jika data lama `NULL`), `Menu` (tampilkan badge halus berisi `catatan` seperti `Ice, Less sugar` tepat di bawah nama menu jika ada), `Kuantitas`, `Subtotal`, `Pajak`, `Total`, dan `Aksi` (`Cetak Struk`).
  - Tombol **Cetak Laporan** di kanan atas wajib meneruskan *query string* filter periode yang sedang aktif ke `print_report.php`.
- **`print_receipt.php` (Struk Kasir 80mm):**
  - Lebar maksimal `320px`, rata tengah, latar putih bersih, garis putus-putus.
  - Menampilkan: Logo/Nama Starbuck, Nomor Transaksi, Tanggal & Waktu (WITA), **Nama Kasir** (`-` jika data lama), rincian Nama Menu, Kuantitas x Harga, **`Catatan` pesanan** (contoh: `Catatan: Ice, Less sugar` di bawah nama minuman apabila tidak kosong), Subtotal, Pajak (11%), Total, dan ucapan terima kasih.
  - `@media print` menyembunyikan seluruh tombol navigasi dan mencetak struk dengan bersih.
- **`print_report.php` (Laporan Penjualan):**
  - Menggunakan logika filter tanggal/bulan WITA yang sama persis dengan `list.php`.
  - Menampilkan keterangan **Periode Laporan** di bagian atas, tabel rekap transaksi lengkap dengan kolom Kasir dan Catatan menu, serta baris **Total Pendapatan** di bagian bawah tabel.

---

## 6. Aksesibilitas & Responsif
- Seluruh kontrol form dan chip pilihan suhu/gula dapat diakses dengan keyboard (`focus-visible` jelas, atribut `aria-pressed` pada chip, dan `aria-live="polite"` pada total tagihan).
- Tampilan wajib rapi dan bebas dari *horizontal scroll* liar pada ukuran layar **375px (Mobile)**, **768px (Tablet)**, dan **1440px (Desktop)**.

---

## 7. Keamanan & Validasi Backend
- Hitung ulang `subtotal`, `pajak` (11%), dan `total` di sisi server berdasarkan `harga` asli dari tabel `produk` di database (abaikan angka harga/total yang dikirim dari form HTML).
- Validasi nilai suhu (`Ice`, `Hot`) dan gula (`No sugar`, `Less sugar`, `Normal`, `High sugar`) di sisi server: hanya simpan ke kolom `catatan` apabila produk benar-benar berkategori `Minuman`; jika produk berkategori `Tambahan` atau `Makanan`, paksa nilai `catatan = NULL`.
- Validasi upload gambar produk: hanya izinkan ekstensi dan MIME type `jpg`, `jpeg`, `png`, `webp` dengan ukuran maksimal 2 MB dan penamaan file unik.

---

## 8. Arsitektur PHP OOP (Dikerjakan pada Tahap 8)

Setelah seluruh tampilan dan fitur pada Tahap 1–7 berjalan lancar, rapikan kode backend ke dalam folder `app/` dengan prinsip *Object-Oriented Programming* (OOP) tanpa mengubah URL halaman maupun atribut `action` form:

```text
app/
  autoload.php                // spl_autoload_register sederhana untuk namespace/kelas di app/
  Database.php                // Koneksi MySQLi singleton (WITA +08:00) & helper prepared statement
  Models/
    Produk.php                // semua(), cari($id), tambah(), ubah(), hapus(), bisaDihapus($id)
    Kasir.php                 // semua(), cari($id), tambah(), ubah(), hapus(), bisaDihapus($id)
    Transaksi.php             // buat(), ambilRiwayat($filter), cariDetail($id), statistik(), pendapatan7Hari(), menuTerlaris($limit)
  Services/
    HitungTransaksi.php       // const PAJAK = 0.11; hitung(int $harga, int $qty): array
    PilihanPesanan.php        // const SUHU, GULA; susunCatatan(?string $kategori, ?string $suhu, ?string $gula): ?string
  Support/
    Format.php                // rupiah(), tanggalIndo(), waktuWita()
    Flash.php                 // set() dan get() pesan notifikasi session untuk komponen Toast
    Upload.php                // unggahGambar() dan hapusGambar() untuk foto produk
```

**Ketentuan Refactor OOP:**
- `config/config.php` memuat `app/autoload.php` dan tetap menyediakan variabel `$koneksi = Database::ambilKoneksi();` agar kompatibilitas terjaga.
- Method `Transaksi::buat()` wajib menggunakan transaksi database (`begin_transaction()`, `commit()`, `rollback()`) saat menyimpan ke tabel `transaksi` dan `detail_transaksi`.
- Lakukan refactor secara bertahap per modul (Produk $\rightarrow$ Kasir $\rightarrow$ Transaksi $\rightarrow$ Dashboard) dan pastikan aplikasi tetap berjalan normal di setiap langkah.

---

## 9. Hal yang Dikecualikan (Out of Scope)
- Tidak ada banner *hero* interaktif ala *landing page*, animasi GSAP, atau partikel biji kopi.
- Tidak ada keranjang multi-item dalam satu transaksi (tetap 1 jenis menu per transaksi sesuai struktur form tugas).
- Tidak ada kolom stok produk, pilihan ukuran cup (*Tall/Grande/Venti* sebagai opsi terpisah), maupun kalkulator uang bayar/kembalian.

---

## 10. Urutan Eksekusi Bertahap (Berhenti & Laporkan di Tiap Tahap)

- **Tahap 0 — Audit Kode (Tanpa Mengubah File):** Baca `config/config.php`, `include/*`, seluruh file di `pages/`, dan `process/`. Laporkan: (a) cara pembuatan ID pada keempat tabel saat ini, (b) struktur query `INSERT` transaksi dan cara pengisian `id_nota`, (c) lokasi folder upload foto produk, (d) nama field form di `transactions/add.php`, dan (e) status kolom `kategori` serta `catatan` di database. Tunggu konfirmasi.
- **Tahap 1 — Migrasi SQL & Form Kasir AUTO_INCREMENT:** Pastikan SQL Bagian 2.1 sudah dijalankan, hapus input manual `ID Kasir` di `cashiers/add.php`, dan perbarui proses `INSERT` kasir agar memakai `AUTO_INCREMENT`.
- **Tahap 2 — Fondasi Desain Earthy Starbucks, Duet Font, & Sidebar Box Indicator:** Terapkan palet warna Bagian 4.1 di `assets/css/style.css`, muat duet font `Plus Jakarta Sans` + `Inter` (`tabular-nums`), buat `assets/js/app.js` (Toast & Modal konfirmasi), dan perbaiki indikator aktif sidebar menjadi kotak membulat `12px` berwarna `#00754A` di dalam sidebar yang bergeser halus.
- **Tahap 3 — Modul Produk & Kategori:** Tambahkan `<select name="kategori">` di form tambah/edit produk, simpan ke database, dan tampilkan badge kategori beserta foto produk pada *card grid* `products/list.php` (tanpa stok).
- **Tahap 4 — Transaksi Baru (`add.php`): Kasir Bertugas & Kustomisasi Minuman:** Tambahkan dropdown wajib Kasir Bertugas (`localStorage`), tambahkan pemilih chip Suhu (`Ice`/`Hot`) dan Gula (`No sugar`/`Less sugar`/`Normal`/`High sugar`) yang hanya muncul untuk kategori `Minuman` dengan default cerdas (`No sugar` untuk Americano/Pike Place/Espresso, `Normal` untuk minuman lain), serta simpan `id_kasir`, `kategori`, `kuantitas_harga`, dan `catatan` dengan validasi penuh di server.
- **Tahap 5 — Riwayat Berfilter Periode (WITA), Cetak Struk 80mm, & Laporan:** Terapkan filter periode tanggal/bulan wajib di `transactions/list.php` (default bulan berjalan WITA), tampilkan nama Kasir dan badge `catatan` minuman di tabel riwayat, struk 80mm (`print_receipt.php`), dan laporan cetak (`print_report.php`).
- **Tahap 6 — Dashboard Operasional:** Bangun ulang `pages/dashboard.php` dengan 4 kartu statistik (kartu ke-4 Total Pendapatan berlatar `#1E3932`), grafik batang pendapatan 7 hari terakhir (WITA), daftar 5 Menu Terlaris dengan thumbnail, dan tabel 5 Transaksi Terbaru.
- **Tahap 7 — Modul Kasir & Proteksi Foreign Key:** Rapikan tabel kasir, integrasikan modal konfirmasi hapus dan notifikasi Toast, serta tangani penghapusan kasir/produk yang sudah dipakai di transaksi agar menampilkan pesan ramah.
- **Tahap 8 — Refactor Backend ke PHP OOP (`app/`):** Buat struktur kelas di folder `app/` sesuai Bagian 8 dan hubungkan ke `process/` serta `pages/` secara bertahap dengan *prepared statements* dan transaksi database.
- **Tahap 9 — QA Akhir & Audit Responsif:** Verifikasi seluruh daftar periksa pada Bagian 11 dan uji tampilan pada lebar layar `375px`, `768px`, dan `1440px`.

---

## 11. Kriteria Selesai (Acceptance Criteria)
- [ ] Seluruh halaman berjalan tanpa *error* PHP maupun SQL; zona waktu `Asia/Makassar` (WITA) konsisten di seluruh fitur.
- [ ] Kolom `kasir.id_kasir` sudah `AUTO_INCREMENT` dan form Tambah Kasir tidak lagi meminta input ID manual.
- [ ] Kolom `produk.kategori` dan `detail_transaksi.catatan` berfungsi penuh dari form hingga ke struk dan laporan.
- [ ] Pilihan Suhu (`Ice`/`Hot`) dan Gula (`No sugar`/`Less sugar`/`Normal`/`High sugar`) hanya muncul saat memilih produk berkategori `Minuman` (dengan default `No sugar` untuk Americano/kopi hitam dan `Normal` untuk minuman lain), serta otomatis disembunyikan (`NULL`) untuk kategori `Tambahan` dan `Makanan`.
- [ ] Transaksi baru mencatat `id_kasir`, `kategori`, `kuantitas_harga`, dan `catatan` dengan perhitungan ulang `subtotal`, `pajak` (11%), dan `total` di server.
- [ ] Riwayat transaksi (`list.php`) menampilkan transaksi bulan berjalan secara bawaan, memiliki filter Tanggal & Bulan yang terhubung ke Cetak Laporan, dan bersifat *read-only*.
- [ ] Dashboard menampilkan 4 kartu statistik (dengan focal card hijau `#1E3932`), grafik batang pendapatan 7 hari WITA, 5 menu terlaris, dan 5 transaksi terbaru.
- [ ] Indikator aktif di sidebar berbentuk kotak membulat `12px` rapi di dalam sidebar (tidak menonjol keluar menabrak teks) dan berpindah secara halus.
- [ ] Duet font `Plus Jakarta Sans` (judul/nama menu/tombol) dan `Inter` (`tabular-nums` untuk angka/harga/tabel) diterapkan secara konsisten bersama palet warna Earthy Starbucks.
- [ ] Penghapusan data memakai Modal Bootstrap, umpan balik memakai Toast, dan data yang terikat *foreign key* terlindungi dari *fatal error*.
- [ ] Arsitektur PHP OOP di folder `app/` berjalan bersih menggunakan *prepared statements* dan *database transaction*.