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

<div class="container">
    <div class="row">
        <div class="col-3"></div>
        <div class="col-6 mt-5">
            <form action="/starbuck/process/process_products.php" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $id_val; ?>">
                <input type="hidden" name="id_produk" value="<?php echo $id_val; ?>">

                <div class="form-floating mt-5">
                    <label for="nama_produk">Nama Produk</label>
                    <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($row['nama_produk'] ?? ''); ?>"
                        class="form-control" id="nama_produk" placeholder="Nama Produk" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="harga">Harga</label>
                    <input type="number" name="harga" value="<?php echo $row['harga'] ?? ''; ?>"
                        class="form-control" id="harga" placeholder="Harga" required>
                </div>
                <div class="mt-5 d-flex gap-2">
                    <button type="submit" class="btn-solid theme-warning">Edit Data</button>
                    <a href="list.php" class="btn-solid theme-secondary">Kembali</a>
                </div>
            </form>
        </div>
        <div class="col-3"></div>
    </div>
</div>

<?php
include ROOTPATH . '/include/footer.php';
?>
