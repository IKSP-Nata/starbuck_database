<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$total_kasir = 0;
$res_kasir = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kasir");
if ($res_kasir) {
    $row_kasir = mysqli_fetch_assoc($res_kasir);
    $total_kasir = $row_kasir['total'];
}

$total_produk = 0;
$res_produk = mysqli_query($koneksi, "SHOW TABLES LIKE 'produk'");
if ($res_produk && mysqli_num_rows($res_produk) > 0) {
    $rp = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk");
    if ($rp) {
        $row_produk = mysqli_fetch_assoc($rp);
        $total_produk = $row_produk['total'];
    }
}

$total_transaksi = 0;
$res_trans = mysqli_query($koneksi, "SHOW TABLES LIKE 'transaksi'");
if ($res_trans && mysqli_num_rows($res_trans) > 0) {
    $rt = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi");
    if ($rt) {
        $row_trans = mysqli_fetch_assoc($rt);
        $total_transaksi = $row_trans['total'];
    }
}

$total_pendapatan = 0;
$res_pend = mysqli_query($koneksi, "SHOW TABLES LIKE 'transaksi'");
if ($res_pend && mysqli_num_rows($res_pend) > 0) {
    $rpe = mysqli_query($koneksi, "SELECT SUM(total) AS total FROM transaksi");
    if ($rpe) {
        $row_pend = mysqli_fetch_assoc($rpe);
        $total_pendapatan = $row_pend['total'] ?? 0;
    }
}

$kasir_terbaru = [];
$res_kasir_list = mysqli_query($koneksi, "SELECT * FROM kasir ORDER BY id_kasir DESC LIMIT 5");
if ($res_kasir_list) {
    while ($row = mysqli_fetch_assoc($res_kasir_list)) {
        $kasir_terbaru[] = $row;
    }
}

$transaksi_terbaru = [];
if ($res_trans && mysqli_num_rows($res_trans) > 0) {
    $res_trans_list = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id_transaksi DESC LIMIT 5");
    if ($res_trans_list) {
        while ($row = mysqli_fetch_assoc($res_trans_list)) {
            $transaksi_terbaru[] = $row;
        }
    }
}

// Query daftar produk untuk Dashboard Hero (PRD-Dashboard-Hero Bagian 5.1)
$heroProducts = [];
if ($res_produk && mysqli_num_rows($res_produk) > 0) {
    $res_hero_produk = mysqli_query($koneksi, "SELECT id_produk, nama_produk, harga, gambar FROM produk ORDER BY id_produk ASC");
    if ($res_hero_produk) {
        while ($row = mysqli_fetch_assoc($res_hero_produk)) {
            $id_prod   = (int)($row['id_produk'] ?? 0);
            $nama_raw  = trim((string)($row['nama_produk'] ?? ''));
            $harga_num = (float)($row['harga'] ?? 0);
            $gambar    = trim((string)($row['gambar'] ?? ''));
            $has_img   = $gambar !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar));
            $gambarUrl = $has_img ? '/starbuck/assets/img/products/' . rawurlencode(basename($gambar)) : '';
            $inisial   = strtoupper(substr($nama_raw !== '' ? $nama_raw : 'PR', 0, 2));

            $heroProducts[] = [
                'id'          => $id_prod,
                'nama'        => $nama_raw,
                'harga'       => $harga_num,
                'hargaFormat' => 'Rp ' . number_format($harga_num, 0, ',', '.'),
                'gambarUrl'   => $gambarUrl,
                'inisial'     => $inisial,
            ];
        }
    }
}

$firstProduct = $heroProducts[0] ?? [
    'id'          => 0,
    'nama'        => 'Menu Starbuck',
    'harga'       => 0,
    'hargaFormat' => 'Rp 0',
    'gambarUrl'   => '',
    'inisial'     => 'SB',
];

// Pecah nama produk menjadi maksimal 2 baris (kata terakhir turun ke baris kedua bila > 2 kata)
$titleWords = preg_split('/\s+/', trim($firstProduct['nama']), -1, PREG_SPLIT_NO_EMPTY);
if ($titleWords && count($titleWords) > 2) {
    $lastWord = array_pop($titleWords);
    $firstTitleLines = [implode(' ', $titleWords), $lastWord];
} else {
    $firstTitleLines = [$firstProduct['nama'] !== '' ? $firstProduct['nama'] : 'Menu Starbuck'];
}
?>

<link rel="stylesheet" href="/starbuck/assets/css/dashboard-hero.css">

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Ringkasan Operasional Starbuck</p>
        </div>
    </div>

    <!-- Kartu Hero Interaktif (PRD-Dashboard-Hero Bagian 3 & 4) -->
    <section class="hero" id="dashboard-hero" aria-label="Menu Pilihan Starbuck">
        <div class="hero-backdrop" id="hero-backdrop" aria-hidden="true"></div>

        <div class="hero-ghost-wrap" aria-hidden="true">
            <div class="hero-ghost-text" id="hero-ghost-text"><?php echo htmlspecialchars($firstProduct['nama'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="hero-grid">
            <!-- Kolom Kiri: Label, Judul (maks 2 baris), Harga, Tombol Pesan Sekarang -->
            <div class="hero-col-left">
                <div class="hero-eyebrow">Menu Pilihan</div>

                <div id="hero-live-region" aria-live="polite" aria-atomic="true">
                    <h2 class="hero-title" id="hero-title">
                        <?php foreach ($firstTitleLines as $line): ?>
                            <span class="hero-title-line">
                                <span class="hero-title-line-inner"><?php echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </h2>
                    <div class="hero-price" id="hero-price"><?php echo htmlspecialchars($firstProduct['hargaFormat'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <a href="/starbuck/pages/transactions/add.php<?php echo $firstProduct['id'] > 0 ? '?id_produk=' . urlencode((string)$firstProduct['id']) : ''; ?>"
                   class="hero-cta" id="hero-order-btn">Pesan Sekarang</a>
            </div>

            <!-- Kolom Tengah: Foto Produk Utama & Fallback Inisial 2 Huruf -->
            <div class="hero-col-center">
                <div class="hero-stage" id="hero-stage">
                    <img src="<?php echo htmlspecialchars($firstProduct['gambarUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($firstProduct['nama'], ENT_QUOTES, 'UTF-8'); ?>"
                         class="hero-product-img<?php echo $firstProduct['gambarUrl'] === '' ? ' d-none' : ''; ?>"
                         id="hero-product-img"
                         onerror="this.classList.add('d-none'); if(this.nextElementSibling){this.nextElementSibling.classList.remove('d-none');}">
                    <div class="hero-fallback-avatar<?php echo $firstProduct['gambarUrl'] !== '' ? ' d-none' : ''; ?>"
                         id="hero-fallback-avatar"
                         aria-hidden="true"><?php echo htmlspecialchars($firstProduct['inisial'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <!-- Kolom Kanan: Tagline, Subteks, Strip Thumbnail, Indikator 1 / N -->
            <div class="hero-col-right">
                <p class="hero-tagline">Pilih menu favoritmu</p>
                <p class="hero-subtext">Klik untuk melihat detail menu</p>

                <div class="hero-thumbs" id="hero-thumbs" role="group" aria-label="Daftar menu produk">
                    <?php foreach ($heroProducts as $idx => $prod):
                        $isActive = ($idx === 0);
                        $prodNama = htmlspecialchars($prod['nama'], ENT_QUOTES, 'UTF-8');
                        $prodImg  = htmlspecialchars($prod['gambarUrl'], ENT_QUOTES, 'UTF-8');
                        $prodInit = htmlspecialchars($prod['inisial'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <button type="button"
                            class="hero-thumb<?php echo $isActive ? ' is-active' : ''; ?>"
                            data-index="<?php echo (int)$idx; ?>"
                            data-id="<?php echo (int)$prod['id']; ?>"
                            aria-label="Pilih <?php echo $prodNama; ?>"
                            aria-pressed="<?php echo $isActive ? 'true' : 'false'; ?>">
                            <?php if ($prod['gambarUrl'] !== ''): ?>
                                <img src="<?php echo $prodImg; ?>"
                                     alt=""
                                     class="hero-thumb-img"
                                     loading="lazy"
                                     onerror="this.classList.add('d-none'); if(this.nextElementSibling){this.nextElementSibling.classList.remove('d-none');}">
                                <span class="hero-thumb-fallback d-none" aria-hidden="true"><?php echo $prodInit; ?></span>
                            <?php else: ?>
                                <span class="hero-thumb-fallback" aria-hidden="true"><?php echo $prodInit; ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="hero-counter" id="hero-counter">
                    <?php echo count($heroProducts) > 0 ? '1 / ' . count($heroProducts) : '0 / 0'; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- 4 Kartu Statistik Sejajar (PRD 4.2, jarak 32px di bawah hero) -->
    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Kasir</div>
                    <div class="metric-value fw-bold mb-3"><?php echo (int)$total_kasir; ?></div>
                </div>
                <div>
                    <a href="/starbuck/pages/cashiers/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Produk</div>
                    <div class="metric-value fw-bold mb-3"><?php echo (int)$total_produk; ?></div>
                </div>
                <div>
                    <a href="/starbuck/pages/products/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Transaksi</div>
                    <div class="metric-value fw-bold mb-3"><?php echo (int)$total_transaksi; ?></div>
                </div>
                <div>
                    <a href="/starbuck/pages/transactions/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Pendapatan</div>
                    <div class="metric-value fw-bold mb-3">Rp <?php echo number_format((float)$total_pendapatan, 0, ',', '.'); ?></div>
                </div>
                <div>
                    <a href="/starbuck/pages/transactions/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

    </div>

    <!-- Kartu Putih Transaksi Terbaru (PRD 4.2) -->
    <div class="card-soft card-soft-flush">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
            <div>
                <h2 class="h5 fw-semibold mb-1">Transaksi Terbaru</h2>
                <p class="page-subtitle mb-0">5 transaksi terakhir yang tercatat</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="/starbuck/pages/transactions/add.php" class="btn-coffee">+ Transaksi Baru</a>
                <a href="/starbuck/pages/transactions/list.php" class="btn-coffee-outline">Lihat Semua</a>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table-soft">
                <thead>
                    <tr>
                        <th scope="col" class="col-index">No</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Waktu (WITA)</th>
                        <th scope="col" class="col-money">Subtotal</th>
                        <th scope="col" class="col-money">Pajak (11%)</th>
                        <th scope="col" class="col-money">Total</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi_terbaru)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Belum ada transaksi yang tercatat. Klik "+ Transaksi Baru" untuk memulai.</td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($transaksi_terbaru as $trx): ?>
                    <tr>
                        <td class="col-index"><?php echo $no++; ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars($trx['tanggal']); ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars($trx['waktu']); ?></td>
                        <td class="col-money">Rp <?php echo number_format((float)$trx['subtotal'], 0, ',', '.'); ?></td>
                        <td class="col-money">Rp <?php echo number_format((float)$trx['pajak'], 0, ',', '.'); ?></td>
                        <td class="col-money fw-bold">Rp <?php echo number_format((float)$trx['total'], 0, ',', '.'); ?></td>
                        <td class="text-end">
                            <a href="/starbuck/pages/transactions/print_receipt.php?id_transaksi=<?php echo urlencode($trx['id_transaksi']); ?>"
                               target="_blank" class="btn-coffee-outline btn-sm-soft">Cetak Struk</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>window.HERO_PRODUCTS = <?php echo json_encode($heroProducts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="/starbuck/assets/js/dashboard-hero.js"></script>

<?php include ROOTPATH . '/include/footer.php'; ?>
