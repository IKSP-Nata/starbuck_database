<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$id = $_GET['id'] ?? $_GET['id_produk'] ?? '';

// Deteksi nama kolom primary key (id atau id_produk)
$col_id = 'id';
$check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
if ($check_col && mysqli_num_rows($check_col) > 0) {
    $col_id = 'id_produk';
}

$query = "SELECT * FROM produk WHERE $col_id = '$id'";
$result = mysqli_query($koneksi, $query);
$row = mysqli_fetch_assoc($result);
$id_val = $row[$col_id] ?? ($row['id'] ?? ($row['id_produk'] ?? $id));
?>

<div class="container-fluid px-0">
    <div class="card-soft card-narrow mt-2">
        <div class="mb-4">
            <h1 class="page-title">Edit Produk</h1>
            <p class="page-subtitle">Perbarui informasi nama menu atau harga jual</p>
        </div>

        <form action="/starbuck/process/process_products.php" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_val, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars($id_val, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
                <label for="nama_produk" class="form-label fw-medium">Nama Produk</label>
                <input type="text" name="nama_produk"
                    value="<?php echo htmlspecialchars($row['nama_produk'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control" id="nama_produk" placeholder="Nama Produk" required>
            </div>

            <div class="mb-4">
                <label for="harga" class="form-label fw-medium">Harga (Rp)</label>
                <input type="number" name="harga"
                    value="<?php echo htmlspecialchars($row['harga'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="harga" placeholder="Harga" min="0" required>
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
