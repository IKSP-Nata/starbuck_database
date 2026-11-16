# PRD: Redesign UI Starbuck POS (Warm Minimalist Coffee POS)

> Dokumen ini adalah instruksi eksekusi untuk AI coding agent di Antigravity.
> Baca SELURUH dokumen ini serta file `SKILL.md` di root proyek sebelum menulis kode. Jika ada konflik antara desain referensi dan aturan database, **aturan database selalu menang**.

---

## 0. Konteks Singkat

- **Proyek:** Aplikasi kasir kopi "Starbuck", PHP Native + MySQLi + Bootstrap 5, berjalan di Laragon dengan database MySQL `starbuck`.
- **Tujuan:** Merombak TAMPILAN saja agar mengikuti gaya *Warm Minimalist Coffee POS* (sidebar kiri, area menu berupa kartu, dan panel **Bills** di kanan, dengan palet cokelat hangat).
- **Bukan tujuan:** Menambah fitur baru, mengubah struktur tabel database, atau mengubah logika backend.
- **File pendukung:** `SKILL.md` (berada di root folder proyek `starbuck/`).

---

## 1. ATURAN KERAS (Jangan Dilanggar)

### 1.1 Backend dan Struktur
1. Jangan ubah nama variabel PHP, nama kolom, query SQL, maupun file di folder `process/`.
2. Pertahankan struktur: `config/config.php`, `include/header.php`, `include/footer.php`, `pages/dashboard.php`, `pages/cashiers/`, `pages/products/`, `pages/transactions/`, `process/`.
3. Pertahankan konstanta `ROOTPATH`, variabel koneksi `$koneksi`, dan `date_default_timezone_set('Asia/Makassar');`.
4. Semua `action` form dan `href` navigasi harus tetap mengarah ke file yang sama seperti sebelumnya.
5. Database hanya punya 4 tabel: `kasir`, `produk`, `transaksi`, `detail_transaksi`. Jangan menambah tabel atau kolom.

### 1.2 Aturan Bisnis
- 1 transaksi = 1 jenis menu + jumlah beli.
- `subtotal = harga * jumlah`, `pajak = subtotal * 0.11` (dikunci 11%), `total = subtotal + pajak`. Dihitung di JS (frontend) dan PHP (backend).
- Menambah transaksi **tidak mengurangi stok**.
- `transactions/list.php` **read-only**: tanpa tombol Edit/Hapus. Hanya **Cetak Struk** (`print_receipt.php?id_transaksi=...`, `target="_blank"`) dan **Cetak Laporan** (`print_report.php`).

### 1.3 Daftar Elemen Referensi yang DILARANG Ditiru
Gambar referensi punya banyak elemen yang tidak ada di database. Jangan dibuat, bahkan sebagai dekorasi:

| Elemen di gambar referensi | Keputusan |
|---|---|
| Filter kategori (All, Coffee, Juice, dst.) | **Hapus.** Tidak ada kolom kategori. |
| Mood (panas/dingin), Size S/M/L, Sugar, Ice | **Hapus.** |
| Tombol Notes pada item Bills | **Hapus.** |
| Payment Method (Cash/Debit/E-Wallet) | **Hapus.** |
| Menu Wallet, Promos, Setting, Logout, History | **Hapus.** Sidebar hanya 4 tautan (lihat 4.1). |
| Foto produk | **Jangan pakai.** Tidak ada kolom gambar. Gunakan avatar inisial (lihat 3.4). |
| Deskripsi produk | **Hapus.** Tidak ada kolom deskripsi. |
| Banyak item di Bills (multi-item) | **Hapus.** Hanya 1 item. |
| Kartu profil kasir + lonceng notifikasi | **Hapus.** Tidak ada sistem login/notifikasi. |
| Uang bayar dan kembalian | **Hapus.** |

Yang **diambil dari referensi:** tata letak, suasana hangat, sudut membulat, kartu putih di atas canvas off-white, kartu menu terpilih dengan border cokelat, panel Bills dengan garis putus-putus sebelum Total, dan tombol cokelat penuh di bawah.

---

## 2. Sistem Desain

### 2.1 CSS Variables (`assets/css/style.css`)
```css
:root {
  --bg-canvas: #F8F6F3;
  --bg-surface: #FFFFFF;
  --bg-subtle: #F3EFEA;
  --primary-coffee: #4E342E;
  --primary-hover: #3B2621;
  --text-main: #2C221E;
  --text-muted: #7A6F69;
  --border-soft: #EAE4DD;
  --danger-soft-bg: #FBEDEA;
  --danger-soft-text: #A3382B;
  --radius-card: 18px;
  --radius-control: 12px;
  --shadow-soft: 0 4px 20px rgba(44, 34, 30, 0.04);
}
```
Jangan menambah warna aksen baru di luar palet ini kecuali `--danger-soft-*` untuk tombol Hapus.

### 2.2 Tipografi
- Font: **Plus Jakarta Sans** (Google Fonts), bobot 400, 500, 600, 700. Fallback `system-ui, sans-serif`.
- Judul halaman: 28px, `600`, `letter-spacing: -0.02em`.
- Subjudul dan label sekunder: 14px, warna `--text-muted`.
- Angka metrik dan nominal: `700`, `font-variant-numeric: tabular-nums` agar digit sejajar.
- Hierarki harus jelas: maksimal 3 ukuran teks per layar (judul, isi, keterangan).

### 2.3 Komponen
- Kartu: `background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-soft); border: 1px solid var(--border-soft);`
- Tombol utama: latar `--primary-coffee`, teks putih, radius 12px, tinggi 44px, hover `--primary-hover`.
- Tombol sekunder: outline `--primary-coffee`, latar transparan.
- Tombol Hapus: latar `--danger-soft-bg`, teks `--danger-soft-text`, tanpa border.
- Input: gunakan `<label class="form-label fw-medium">` DI ATAS `<input class="form-control">` atau `<select class="form-select">`. **Dilarang `form-floating`.**
- Fokus input: border `--primary-coffee` + ring `0 0 0 3px rgba(78,52,46,.12)`.
- Format uang: PHP `'Rp ' . number_format($x, 0, ',', '.')`, JS `'Rp ' + n.toLocaleString('id-ID')`.

### 2.4 Ikon
- Boleh memakai **Bootstrap Icons** (outline) kecil (20px) **hanya di sidebar** dan search box. Tidak ada ikon di judul, kartu statistik, atau tombol aksi tabel.
- Dilarang emoji sebagai ikon.

---

## 3. Panduan Anti "AI Slop"

Tampilan hasil AI sering terlihat generik. Hindari ciri berikut dan terapkan lawannya.

### 3.1 Yang HARUS dihindari
- Gradien ungu/biru, glassmorphism, neon glow, bayangan tebal.
- Semua elemen berukuran dan berjarak sama sehingga tidak ada hierarki.
- Kartu statistik dengan ikon bulat berwarna-warni di tiap kartu.
- Badge dan tombol warna-warni (hijau, biru, kuning, merah bersamaan).
- Emoji di judul, tombol, atau label.
- Teks placeholder generik ("Lorem ipsum", "John Doe", "Welcome back!").
- Tabel Bootstrap default (striped, border penuh, header biru/abu tua).
- Hover animasi berlebihan (membesar, memantul, berputar).
- Teks rata tengah di mana-mana.

### 3.2 Yang HARUS diterapkan
- **Satu warna aksen saja** (`--primary-coffee`). Selebihnya netral hangat.
- **Ruang kosong yang lega:** padding kartu 24px, jarak antar-kartu 24px, jarak antar-baris tabel `py-3`.
- **Hierarki tegas:** judul besar dan tebal, label kecil dan muted. Nominal Total dibuat paling besar di panel Bills.
- **Border 1px halus + bayangan sangat tipis**, bukan border tebal.
- **Mikro-interaksi halus saja:** transisi `150ms ease` pada hover, perubahan warna latar, bukan pergerakan besar.
- **Status kosong yang berkarakter:** misalnya panel Bills belum ada menu: tulis "Pilih menu di sebelah kiri untuk memulai transaksi." (bukan "No data").
- **Copy bahasa Indonesia yang natural dan singkat.** Konsisten istilah: Kasir, Produk, Transaksi, Menu.
- **Detail kecil yang rapi:** garis putus-putus sebelum Total, `tabular-nums` untuk angka, header tabel uppercase 12px dengan `letter-spacing`, nomor urut rata kanan/tengah konsisten.
- **Fokus keyboard terlihat** (aksesibilitas) dan kontras teks minimal 4.5:1.

### 3.3 Cek Sebelum Selesai
Ulangi pertanyaan ini untuk tiap halaman:
1. Apakah hanya ada satu warna aksen?
2. Apakah ada elemen yang tidak punya kolom di database?
3. Apakah ada emoji atau gradien?
4. Apakah angka rata dan format `Rp` konsisten?
5. Apakah halaman terlihat rapi di lebar 375px, 768px, dan 1440px?

### 3.4 Pengganti Foto Produk (Tanpa Kolom Gambar)
Karena tidak ada kolom gambar, buat **avatar inisial** di kartu menu:
- Kotak 56x56px, radius 14px, latar `--bg-subtle`, teks 2 huruf pertama nama produk, warna `--primary-coffee`, `font-weight: 700`.
- Murni CSS/PHP (`strtoupper(substr($nama, 0, 2))`). Jangan memakai gambar eksternal atau placeholder foto.

---

## 4. Spesifikasi Per Halaman

### 4.1 Layout Utama (`include/header.php`, `include/footer.php`)
- Sidebar kiri **fixed**, lebar `110px` (desktop), latar `--bg-surface`, radius kanan 20px (atau kartu melayang dengan margin 16px seperti referensi).
- Atas: teks **Starbuck** (600, cokelat). Tanpa logo gambar.
- 4 tautan vertikal (ikon 20px di atas label 12px, rata tengah):
  1. Dashboard: `pages/dashboard.php`
  2. Kasir: `pages/cashiers/list.php`
  3. Produk: `pages/products/list.php`
  4. Transaksi: `pages/transactions/list.php`
- Aktif: kotak membulat 12px `--primary-coffee`, teks dan ikon putih. Non-aktif: `--text-muted`, hover `--bg-subtle`.
- Layar < 992px: sidebar berubah menjadi navbar atas dengan tombol collapse Bootstrap.
- Area konten: `margin-left: 110px`, padding 32px, latar `--bg-canvas`.
- `footer.php` menutup pembungkus konten dan memuat Bootstrap 5 JS bundle.
- Hubungkan `assets/css/style.css` di `header.php`.

### 4.2 Dashboard (`pages/dashboard.php`)
- Judul **Dashboard**, subjudul **Ringkasan Operasional Starbuck**.
- 4 kartu statistik sejajar (`row g-4`, `col-md-6 col-xl-3`): Total Kasir, Total Produk, Total Transaksi, Total Pendapatan.
  - Isi kartu: label kecil muted, angka besar `fw-bold` (28-32px), tautan teks `Lihat Data →`. Tanpa ikon, tanpa tombol berwarna.
- Bawah: kartu putih **Transaksi Terbaru** (tabel ringkas) + dua tombol: `+ Transaksi Baru` (utama, ke `transactions/add.php`) dan `Lihat Semua` (outline, ke `transactions/list.php`).

### 4.3 Kasir (`pages/cashiers/`)
- `list.php`: header berisi judul + tombol `+ Tambah Kasir`. Tabel di dalam kartu putih, header `--bg-subtle`, tombol `Edit` (outline netral) dan `Hapus` (soft danger).
- `add.php` / `edit.php`: kartu putih di tengah, `max-width: 560px`, label di atas input, tombol Simpan (utama) dan Batal (outline).

### 4.4 Produk (`pages/products/`)
- `list.php`: header berisi judul, kolom **Pencarian Menu** (live filter JS berdasarkan nama) dan `+ Tambah Produk`.
- Tampilan **card grid** (`row g-4`, `col-md-6 col-xl-4`): avatar inisial, Nama Produk, Harga (`Rp`), informasi Stok, tombol `Edit` dan `Hapus`.
- Tampilkan stok sebagai teks kecil muted (contoh: `Stok 24`), bukan badge warna-warni.
- `add.php` / `edit.php`: kartu 560px seperti halaman Kasir.

### 4.5 Transaksi Baru (`pages/transactions/add.php`): Halaman Utama yang Meniru Referensi
Layout **split-screen**: `col-lg-7` (katalog) dan `col-lg-5` (Bills).

**Kolom kiri: "Pilih Menu"**
- Judul **Pilih Menu**, di kanan teks muted `N menu tersedia`.
- Search box (latar `--bg-subtle`/putih, ikon kaca pembesar) untuk live filter nama produk.
- Grid kartu menu (`row g-3`, `col-md-6`): avatar inisial di kiri, Nama Produk dan Harga di kanan. **Tanpa tombol "Add to Billing", kartu itu sendiri yang bisa diklik.**
- Kartu terpilih: border 2px `--primary-coffee`, latar `#FFFFFF`; kartu lain border `--border-soft`. Hover: latar `--bg-subtle`.
- Implementasi: tiap kartu punya `data-id`, `data-nama`, `data-harga`; klik mengisi `<input type="hidden" name="id_produk">` yang **nama field-nya sama persis dengan form asli**.

**Kolom kanan: "Bills" (sticky)**
- Kartu putih `position-sticky; top: 24px`, judul **Bills** dengan subjudul muted `Ringkasan Transaksi`.
- Menu terpilih: nama produk + harga satuan. Jika kosong, tampilkan teks status kosong (lihat 3.2).
- Kontrol jumlah: tombol `-`, input angka (min `1`), tombol `+` (tombol bulat netral).
- Rincian: Subtotal, Pajak (11%), garis putus-putus `border-top: 1px dashed var(--border-soft)`, **Total** (paling besar, 24px, bold). Update real-time dengan JS.
- Tombol penuh lebar **Simpan & Cetak Struk** (`--primary-coffee`), disabled sampai menu dipilih.
- **Tanpa** Notes, Payment Method, Uang Bayar, Kembalian.

### 4.6 Riwayat dan Cetak
- `list.php`: header berisi `Cetak Laporan` (outline cokelat) dan `+ Transaksi Baru` (utama). Tabel: No, Menu, Jumlah, Subtotal, Pajak, Total, Tanggal, Waktu (WITA), Aksi (`Cetak Struk`). Kolom nominal rata kanan. **Tanpa Edit/Hapus.**
- `print_receipt.php`: struk `max-width: 320px` rata tengah, latar putih, garis putus-putus, tombol `Cetak Struk` (`window.print()`) disembunyikan saat `@media print`.
- `print_report.php`: tabel rekap bersih, baris **Total Pendapatan** di paling bawah, `@media print` menyembunyikan tombol dan sidebar.

---

## 5. Urutan Eksekusi (Kerjakan Bertahap, Berhenti per Tahap)

1. **Audit:** baca `config/config.php`, `include/header.php`, `include/footer.php`, dan semua file `pages/` serta `process/`. Catat nama variabel, kolom, dan `action` form. Laporkan ringkasannya sebelum mengubah apa pun.
2. **Fondasi:** buat `assets/css/style.css` (variabel, tipografi, komponen dasar) dan hubungkan di `header.php`. Ubah `header.php` dan `footer.php` menjadi layout sidebar.
3. **Dashboard.**
4. **Transaksi baru (`add.php`)**: halaman paling penting, kerjakan dengan teliti.
5. **Riwayat dan cetak:** `list.php`, `print_receipt.php`, `print_report.php`.
6. **Produk**, lalu **Kasir.**
7. **QA akhir:** jalankan checklist 3.3 dan checklist di bawah.

## 6. Kriteria Selesai (Acceptance Criteria)

- [ ] Semua halaman bisa dibuka tanpa error PHP/SQL; semua tombol dan form berfungsi seperti sebelum redesign.
- [ ] Tidak ada nama variabel, kolom, atau file `process/` yang berubah.
- [ ] Tidak ada elemen yang tidak punya kolom di database (lihat tabel 1.3).
- [ ] Hitungan Subtotal/Pajak 11%/Total di JS sama dengan hasil di PHP dan tersimpan benar.
- [ ] Stok tidak berkurang setelah transaksi.
- [ ] `transactions/list.php` tidak punya Edit/Hapus.
- [ ] Hanya satu warna aksen, tanpa gradien dan emoji.
- [ ] Responsif di 375px, 768px, 1440px; struk dan laporan tercetak rapi.

## 7. Larangan Tambahan untuk Agent
- Jangan mengganti framework (tetap Bootstrap 5 + CSS kustom + JS vanilla).
- Jangan memasang library baru kecuali Bootstrap Icons dan Google Fonts.
- Jangan membuat ulang file dari nol jika cukup mengedit bagian tampilannya.
- Jika ragu apakah suatu elemen ada di database, **tanyakan dulu** daripada menebak.