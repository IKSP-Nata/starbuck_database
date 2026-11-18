<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['id_produk']) ? (int)$_GET['id_produk'] : 0);

// Deteksi nama kolom primary key (id atau id_produk)
$col_id = 'id';
$check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
if ($check_col && mysqli_num_rows($check_col) > 0) {
    $col_id = 'id_produk';
}

$row = null;
if ($id > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE $col_id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}

$id_val       = $row[$col_id] ?? ($row['id'] ?? ($row['id_produk'] ?? $id));
$kategori_val = $row['kategori'] ?? 'Minuman';
$gambar_lama  = $row['gambar'] ?? '';
$has_gambar   = $gambar_lama !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar_lama));
?>

<div class="container-fluid px-0">
    <div class="card-soft card-narrow mt-2">
        <div class="mb-4">
            <h1 class="page-title">Edit Produk</h1>
            <p class="page-subtitle">Perbarui informasi nama menu, harga jual, kategori, atau foto produk</p>
        </div>

        <form action="/starbuck/process/process_products.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$id_val, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars((string)$id_val, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($gambar_lama, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
                <label for="nama_produk" class="form-label fw-medium">Nama Produk</label>
                <input type="text" name="nama_produk"
                    value="<?php echo htmlspecialchars($row['nama_produk'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control" id="nama_produk" placeholder="Nama Produk" required>
            </div>

            <div class="mb-3">
                <label for="harga" class="form-label fw-medium">Harga (Rp)</label>
                <input type="number" name="harga"
                    value="<?php echo htmlspecialchars($row['harga'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="harga" placeholder="Harga" min="0" required>
            </div>

            <div class="mb-3">
                <label for="kategori" class="form-label fw-medium">Kategori</label>
                <select name="kategori" id="kategori" class="form-select" required>
                    <?php foreach (['Minuman', 'Tambahan', 'Makanan'] as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo ($kategori_val === $opt) ? 'selected' : ''; ?>>
                            <?php echo $opt; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="gambar" class="form-label fw-medium">Foto Produk (Opsional)</label>
                <?php if ($has_gambar): ?>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="product-thumb-frame">
                            <img src="../../assets/img/products/<?php echo rawurlencode(basename($gambar_lama)); ?>"
                                 alt="<?php echo htmlspecialchars($row['nama_produk'] ?? 'Foto Produk', ENT_QUOTES, 'UTF-8'); ?>"
                                 class="product-avatar-img">
                        </span>
                        <span class="page-subtitle mb-0">Foto saat ini. Pilih file baru jika ingin mengganti.</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="gambar" id="gambar" class="form-control"
                    accept="image/png, image/jpeg, image/webp">
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
