<?php
    // Mendefinisikan ROOTPATH sebagai path ke direktori root proyek
    define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
    include ROOTPATH . '/include/header.php';
    include ROOTPATH . '/config/config.php';

    $id = $_GET['id_kasir'];
    $query = "SELECT * FROM kasir WHERE id_kasir = '$id'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
?>

<div class="container">
    <div class="row">
        <div class="col-3"></div>
        <div class="col-6 mt-5">
            <form action="../../process/process_cashiers.php" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_kasir" value="<?php echo $row['id_kasir']; ?>">
                <div class="form-floating mt-5">
                    <label for="floatingInput">Nama Kasir</label>
                    <input type="text" name="nama_kasir" value="<?php echo $row['nama_kasir']; ?>" class="form-control"
                        id="floatingInput" placeholder="Nama Kasir" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="floatingInput">No Telp</label>
                    <input type="number" name="no_telp" value="<?php echo $row['no_telp']; ?>" class="form-control"
                        id="floatingInput" placeholder="No Telp" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="floatingInput">Alamat</label>
                    <input type="text" name="alamat" value="<?php echo $row['alamat']; ?>" class="form-control"
                        id="floatingInput" placeholder="Alamat" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="floatingInput">Email</label>
                    <input type="email" name="email" value="<?php echo $row['email']; ?>" class="form-control"
                        id="floatingInput" placeholder="nama@contoh.com" required>
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
    include '../../include/footer.php';
?>
