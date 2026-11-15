<?php
    define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
    include ROOTPATH . '/include/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-3"></div>
        <div class="col-6 mt-5">
            <form action="../../process/process_cashiers.php" method="post">
                <input type="hidden" name="action" value="add">

                <div class="form-floating mt-5">
                    <label for="floatingInput">Nama Kasir</label>
                    <input type="text" name="nama_kasir" class="form-control" id="floatingInput"
                        placeholder="Nama Kasir" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="floatingInput">No Telp</label>
                    <input type="number" name="no_telp" class="form-control" id="floatingInput" placeholder="No Telp" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="floatingInput">Alamat</label>
                    <input type="text" name="alamat" class="form-control" id="floatingInput" placeholder="Alamat" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="floatingInput">Email</label>
                    <input type="email" name="email" class="form-control" id="floatingInput"
                        placeholder="nama@contoh.com" required>
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
