<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
?>

<div class="container-fluid px-0">
    <div class="card-soft card-narrow mt-2">
        <div class="mb-4">
            <h1 class="page-title">Tambah Produk</h1>
            <p class="page-subtitle">Masukkan nama menu dan harga jual satuan</p>
        </div>

        <form action="/starbuck/process/process_products.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">

            <div class="mb-3">
                <label for="nama_produk" class="form-label fw-medium">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" id="nama_produk"
                    placeholder="Contoh: Caramel Macchiato" required>
            </div>

            <div class="mb-3">
                <label for="harga" class="form-label fw-medium">Harga (Rp)</label>
                <input type="number" name="harga" class="form-control tabular-nums" id="harga"
                    placeholder="Contoh: 45000" min="0" required>
            </div>

            <div class="mb-4">
                <label for="gambar" class="form-label fw-medium">Foto Produk (Opsional)</label>
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
