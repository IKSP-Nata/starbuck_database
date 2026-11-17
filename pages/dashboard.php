<?php
if (!defined('ROOTPATH')) {
    define('ROOTPATH', dirname(__DIR__));
}
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
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Ringkasan Operasional Starbuck</p>
        </div>
    </div>

    <!-- 4 Kartu Statistik Sejajar (col-sm-6 col-lg-3) -->
    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-lg-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Kasir</div>
                    <div class="metric-value fw-bold mb-3"><?php echo (int)$total_kasir; ?></div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/cashiers/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Produk</div>
                    <div class="metric-value fw-bold mb-3"><?php echo (int)$total_produk; ?></div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/products/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Transaksi</div>
                    <div class="metric-value fw-bold mb-3"><?php echo (int)$total_transaksi; ?></div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/transactions/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Pendapatan</div>
                    <div class="metric-value fw-bold mb-3">Rp <?php echo number_format((float)$total_pendapatan, 0, ',', '.'); ?></div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/transactions/list.php" class="link-arrow">Lihat Data &rarr;</a>
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
                <a href="<?php echo $sb_base_url; ?>/pages/transactions/add.php" class="btn-coffee">+ Transaksi Baru</a>
                <a href="<?php echo $sb_base_url; ?>/pages/transactions/list.php" class="btn-coffee-outline">Lihat Semua</a>
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
                            <a href="<?php echo $sb_base_url; ?>/pages/transactions/print_receipt.php?id_transaksi=<?php echo urlencode($trx['id_transaksi']); ?>"
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

<?php include ROOTPATH . '/include/footer.php'; ?>
