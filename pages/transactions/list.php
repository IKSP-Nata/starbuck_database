<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$query = "SELECT t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu,
                 GROUP_CONCAT(
                     CASE
                         WHEN p.nama_produk IS NOT NULL AND dt.kuantitas IS NOT NULL
                             THEN CONCAT(p.nama_produk, ' (x', dt.kuantitas, ')')
                         ELSE p.nama_produk
                     END
                     SEPARATOR ', '
                 ) AS daftar_menu,
                 SUM(dt.kuantitas) AS total_kuantitas
          FROM transaksi t
          LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
          LEFT JOIN produk p ON dt.id_produk = p.id_produk
          GROUP BY t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu
          ORDER BY t.id_transaksi DESC";
$result = mysqli_query($koneksi, $query);
$daftar_transaksi = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daftar_transaksi[] = $row;
    }
}
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Riwayat Transaksi</h1>
            <p class="page-subtitle">Daftar seluruh transaksi penjualan yang tercatat</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="print_report.php" target="_blank" class="btn-coffee-outline">Cetak Laporan</a>
            <a href="/starbuck/pages/transactions/add.php" class="btn-coffee">+ Transaksi Baru</a>
        </div>
    </div>

    <div class="card-soft card-soft-flush">
        <div class="table-wrap">
            <table class="table-soft">
                <thead>
                    <tr>
                        <th scope="col" class="col-index">No</th>
                        <th scope="col">Menu</th>
                        <th scope="col" class="col-num">Kuantitas</th>
                        <th scope="col" class="col-money">Subtotal</th>
                        <th scope="col" class="col-money">Pajak</th>
                        <th scope="col" class="col-money">Total</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Waktu (WITA)</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_transaksi)): ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            Belum ada transaksi yang tercatat. Klik "+ Transaksi Baru" untuk memulai.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($daftar_transaksi as $row): ?>
                    <tr>
                        <td class="col-index"><?php echo $no++; ?></td>
                        <td class="fw-medium">
                            <?php echo !empty($row['daftar_menu']) ? htmlspecialchars($row['daftar_menu']) : '-'; ?>
                        </td>
                        <td class="col-num">
                            <?php echo $row['total_kuantitas'] !== null ? (int)$row['total_kuantitas'] : '-'; ?>
                        </td>
                        <td class="col-money">Rp <?php echo number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                        <td class="col-money">Rp <?php echo number_format((float)$row['pajak'], 0, ',', '.'); ?></td>
                        <td class="col-money fw-bold">Rp <?php echo number_format((float)$row['total'], 0, ',', '.'); ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars($row['waktu']); ?></td>
                        <td class="text-end">
                            <a href="print_receipt.php?id_transaksi=<?php echo urlencode($row['id_transaksi']); ?>"
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

<?php
include ROOTPATH . '/include/footer.php';
?>
