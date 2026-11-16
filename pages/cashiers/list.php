<?php
// Mendefinisikan ROOTPATH sebagai path ke direktori root proyek
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$query = "SELECT * FROM kasir";
$result = mysqli_query($koneksi, $query);
$daftar_kasir = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daftar_kasir[] = $row;
    }
}
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Kasir</h1>
            <p class="page-subtitle">Kelola data petugas kasir operasional Starbuck</p>
        </div>
        <div>
            <a href="/starbuck/pages/cashiers/add.php" class="btn-coffee">+ Tambah Kasir</a>
        </div>
    </div>

    <div class="card-soft card-soft-flush">
        <div class="table-wrap">
            <table class="table-soft">
                <thead>
                    <tr>
                        <th scope="col" class="col-index">No</th>
                        <th scope="col">Nama Kasir</th>
                        <th scope="col">No. Telepon</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Email</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_kasir)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Belum ada data kasir yang terdaftar. Klik "+ Tambah Kasir" untuk menambahkan petugas.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($daftar_kasir as $row): ?>
                    <tr>
                        <td class="col-index py-3"><?php echo $no++; ?></td>
                        <td class="fw-medium py-3"><?php echo htmlspecialchars($row['nama_kasir']); ?></td>
                        <td class="tabular-nums py-3"><?php echo htmlspecialchars($row['no_telp']); ?></td>
                        <td class="py-3"><?php echo htmlspecialchars($row['alamat']); ?></td>
                        <td class="py-3"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="text-end py-3">
                            <div class="d-inline-flex gap-2">
                                <a href="edit.php?id_kasir=<?php echo urlencode($row['id_kasir']); ?>"
                                   class="btn-neutral-outline btn-sm-soft">Edit</a>
                                <a href="/starbuck/process/process_cashiers.php?action=delete&id_kasir=<?php echo urlencode($row['id_kasir']); ?>"
                                   class="btn-soft-danger btn-sm-soft"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                            </div>
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
