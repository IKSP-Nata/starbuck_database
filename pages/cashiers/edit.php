<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$id = isset($_GET['id_kasir']) ? (int)$_GET['id_kasir'] : 0;
$row = Kasir::cari($id);
?>

<div class="container-fluid px-0">
    <div class="card-soft card-narrow mt-2">
        <div class="mb-4">
            <h1 class="page-title">Edit Kasir</h1>
            <p class="page-subtitle">Perbarui informasi kontak atau alamat petugas kasir</p>
        </div>

        <form action="../../process/process_cashiers.php" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_kasir" value="<?php echo htmlspecialchars($row['id_kasir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
                <label for="nama_kasir" class="form-label fw-medium">Nama Kasir</label>
                <input type="text" name="nama_kasir"
                    value="<?php echo htmlspecialchars($row['nama_kasir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control" id="nama_kasir" placeholder="Nama Kasir" required>
            </div>

            <div class="mb-3">
                <label for="no_telp" class="form-label fw-medium">No. Telepon</label>
                <input type="number" name="no_telp"
                    value="<?php echo htmlspecialchars($row['no_telp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="no_telp" placeholder="No. Telepon" required>
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label fw-medium">Alamat</label>
                <input type="text" name="alamat"
                    value="<?php echo htmlspecialchars($row['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control" id="alamat" placeholder="Alamat" required>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label fw-medium">Email</label>
                <input type="email" name="email"
                    value="<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control" id="email" placeholder="nama@starbuck.id" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-coffee">Simpan</button>
                <a href="list.php" class="btn-coffee-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
include ROOTPATH . '/include/footer.php';
?>
