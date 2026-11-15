<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-3"></div>
        <div class="col-6 mt-5">
            <form action="/starbuck/process/process_products.php" method="post">
                <input type="hidden" name="action" value="add">

                <div class="form-floating mt-5">
                    <label for="nama_produk">Nama Produk</label>
                    <input type="text" name="nama_produk" class="form-control" id="nama_produk"
                        placeholder="Nama Produk" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="harga">Harga</label>
                    <input type="number" name="harga" class="form-control" id="harga"
                        placeholder="Harga" required>
                </div>
                <div class="mt-5 d-flex gap-2">
                    <button type="submit" class="btn-solid theme-success">Tambah Data</button>
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

