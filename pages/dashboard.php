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
?>

<div class="container mt-5">

    <div class="mb-4">
        <h4>Dashboard</h4>
        <p class="text-muted">Panel Admin Starbuck</p>
    </div>

    <div class="row g-4 mb-5">

        <div class="col-6 col-md-3">
            <div class="card p-4 h-100" style="border-left: 4px solid #198754;">
                <div class="text-muted mb-1" style="font-size:0.82rem; text-transform:uppercase; letter-spacing:.05em;">Total Kasir</div>
                <div style="font-size:2rem; font-weight:700; line-height:1;"><?php echo $total_kasir; ?></div>
                <a href="/starbuck/pages/cashiers/list.php" class="btn-solid theme-success mt-3" style="font-size:0.8rem;">Lihat Data</a>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card p-4 h-100" style="border-left: 4px solid #0d6efd;">
                <div class="text-muted mb-1" style="font-size:0.82rem; text-transform:uppercase; letter-spacing:.05em;">Total Produk</div>
                <div style="font-size:2rem; font-weight:700; line-height:1;"><?php echo $total_produk; ?></div>
                <a href="/starbuck/pages/products/list.php" class="btn-solid theme-primary mt-3" style="font-size:0.8rem;">Lihat Data</a>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card p-4 h-100" style="border-left: 4px solid #fd7e14;">
                <div class="text-muted mb-1" style="font-size:0.82rem; text-transform:uppercase; letter-spacing:.05em;">Total Transaksi</div>
                <div style="font-size:2rem; font-weight:700; line-height:1;"><?php echo $total_transaksi; ?></div>
                <a href="/starbuck/pages/transactions/list.php" class="btn-solid theme-warning mt-3" style="font-size:0.8rem;">Lihat Data</a>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card p-4 h-100" style="border-left: 4px solid #dc3545;">
                <div class="text-muted mb-1" style="font-size:0.82rem; text-transform:uppercase; letter-spacing:.05em;">Total Pendapatan</div>
                <div style="font-size:1.4rem; font-weight:700; line-height:1.2;">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <a href="/starbuck/pages/transactions/list.php" class="btn-solid theme-danger mt-3" style="font-size:0.8rem;">Lihat Data</a>
            </div>
        </div>

    </div>

    <div class="mb-5">
        <h5 class="mb-3">Akses Cepat</h5>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="/starbuck/pages/cashiers/list.php" class="card p-4 text-center d-block" style="text-decoration:none;">
                    <div style="font-weight:600;">Data Kasir</div>
                    <div class="text-muted mt-1" style="font-size:0.82rem;">Kelola data kasir</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/starbuck/pages/products/list.php" class="card p-4 text-center d-block" style="text-decoration:none;">
                    <div style="font-weight:600;">Data Produk</div>
                    <div class="text-muted mt-1" style="font-size:0.82rem;">Kelola data produk</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/starbuck/pages/transactions/list.php" class="card p-4 text-center d-block" style="text-decoration:none;">
                    <div style="font-weight:600;">Data Transaksi</div>
                    <div class="text-muted mt-1" style="font-size:0.82rem;">Riwayat transaksi</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/starbuck/pages/cashiers/add.php" class="card p-4 text-center d-block" style="text-decoration:none;">
                    <div style="font-weight:600;">Tambah Kasir</div>
                    <div class="text-muted mt-1" style="font-size:0.82rem;">Input kasir baru</div>
                </a>
            </div>
        </div>
    </div>

    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Data Kasir Terbaru</h5>
            <a href="/starbuck/pages/cashiers/list.php" class="btn-solid theme-success" style="font-size:0.8rem;">Lihat Semua</a>
        </div>
        <div class="card">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Kasir</th>
                        <th scope="col">No Telepon</th>
                        <th scope="col">Email</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kasir_terbaru)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data kasir.</td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($kasir_terbaru as $kasir): ?>
                    <tr>
                        <th scope="row"><?php echo $no++; ?></th>
                        <td><?php echo htmlspecialchars($kasir['nama_kasir']); ?></td>
                        <td><?php echo htmlspecialchars($kasir['no_telp']); ?></td>
                        <td><?php echo htmlspecialchars($kasir['email']); ?></td>
                        <td>
                            <a href="/starbuck/pages/cashiers/edit.php?id_kasir=<?php echo $kasir['id_kasir']; ?>" class="btn-solid theme-warning" style="font-size:0.8rem;">Edit</a>
                            <a href="/starbuck/process/process_cashiers.php?action=delete&id_kasir=<?php echo $kasir['id_kasir']; ?>"
                               class="btn-solid theme-danger" style="font-size:0.8rem;"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
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
