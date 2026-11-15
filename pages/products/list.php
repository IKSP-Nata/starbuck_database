<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
?>
<center>
    <a href="/starbuck/pages/products/add.php" class="btn-solid theme-success mt-10 mb-10">Tambah Produk</a>
</center>
<div class="container">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nama Produk</th>
                <th scope="col">Harga</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include ROOTPATH . '/config/config.php';
            $query = "SELECT * FROM produk";
            $result = mysqli_query($koneksi, $query);
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $id_val = $row['id'] ?? $row['id_produk'] ?? '';
            ?>
            <tr>
                <th scope="row"><?php echo $no++; ?></th>
                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $id_val; ?>&id_produk=<?php echo $id_val; ?>" class="btn-solid theme-warning">Edit</a>
                    <a href="/starbuck/process/process_products.php?action=delete&id=<?php echo $id_val; ?>&id_produk=<?php echo $id_val; ?>"
                        class="btn-solid theme-danger"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>
<?php
include ROOTPATH . '/include/footer.php';
?>
