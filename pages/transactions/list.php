<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$filter = getTransaksiFilter($koneksi, $_GET);
$where_sql = $filter['where_sql'];
$query_string = $_SERVER['QUERY_STRING'] ?? '';
$print_report_url = 'print_report.php' . ($query_string !== '' ? '?' . htmlspecialchars($query_string, ENT_QUOTES, 'UTF-8') : '');

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
          $where_sql
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
            <p class="page-subtitle">
                Periode <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($filter['periode_label']); ?></strong>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo $print_report_url; ?>" target="_blank" class="btn-coffee-outline">Cetak Laporan</a>
            <a href="/starbuck/pages/transactions/add.php" class="btn-coffee">+ Transaksi Baru</a>
        </div>
    </div>

    <!-- Kotak Filter Tanggal & Bulan (GET, label di atas input, tanpa form-floating) -->
    <div class="card-soft mb-4">
        <form action="list.php" method="GET" id="form-filter-transaksi">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-3">
                    <label for="tanggal_awal" class="form-label fw-medium">Dari Tanggal</label>
                    <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control tabular-nums"
                        value="<?php echo htmlspecialchars($filter['tanggal_awal'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="tanggal_akhir" class="form-label fw-medium">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control tabular-nums"
                        value="<?php echo htmlspecialchars($filter['tanggal_akhir'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="bulan" class="form-label fw-medium">Pilih Bulan</label>
                    <input type="month" name="bulan" id="bulan" class="form-control tabular-nums"
                        value="<?php echo htmlspecialchars($filter['bulan'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3 d-grid">
                    <button type="submit" class="btn-coffee btn-block">Terapkan Filter</button>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 pt-3 mt-3 border-top" style="border-color: var(--border-soft) !important;">
                <span class="page-subtitle me-1 mb-0">Filter Cepat</span>
                <a href="list.php?mode=hari_ini"
                   class="<?php echo $filter['active_mode'] === 'hari_ini' ? 'btn-coffee' : 'btn-neutral-outline'; ?> btn-sm-soft">
                    Hari Ini
                </a>
                <a href="list.php"
                   class="<?php echo $filter['active_mode'] === 'bulan_ini' ? 'btn-coffee' : 'btn-neutral-outline'; ?> btn-sm-soft">
                    Bulan Ini
                </a>
                <a href="list.php?mode=semua"
                   class="<?php echo $filter['active_mode'] === 'semua' ? 'btn-coffee' : 'btn-neutral-outline'; ?> btn-sm-soft">
                    Semua
                </a>
            </div>
        </form>
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
                            Tidak ada transaksi pada periode ini. Ubah filter di atas atau klik "+ Transaksi Baru".
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

<script>
(function () {
    var inputAwal  = document.getElementById('tanggal_awal');
    var inputAkhir = document.getElementById('tanggal_akhir');
    var inputBulan = document.getElementById('bulan');

    function clearBulan() {
        if (inputBulan && (inputAwal.value !== '' || inputAkhir.value !== '')) {
            inputBulan.value = '';
        }
    }

    if (inputAwal) {
        inputAwal.addEventListener('change', clearBulan);
    }
    if (inputAkhir) {
        inputAkhir.addEventListener('change', clearBulan);
    }
    if (inputBulan) {
        inputBulan.addEventListener('change', function () {
            if (inputBulan.value !== '') {
                if (inputAwal) inputAwal.value = '';
                if (inputAkhir) inputAkhir.value = '';
            }
        });
    }
})();
</script>

<?php
include ROOTPATH . '/include/footer.php';
?>
