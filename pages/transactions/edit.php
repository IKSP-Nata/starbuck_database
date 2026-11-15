<?php
    define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
    include ROOTPATH . '/include/header.php';
    include ROOTPATH . '/config/config.php';

    $id = $_GET['id_transaksi'] ?? '';
    $query = "SELECT * FROM transaksi WHERE id_transaksi = '$id'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
?>

<div class="container">
    <div class="row">
        <div class="col-3"></div>
        <div class="col-6 mt-5">
            <form action="../../process/process_transactions.php" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_transaksi" value="<?php echo $row['id_transaksi']; ?>">

                <div class="form-floating mt-5">
                    <label for="subtotal">Subtotal</label>
                    <input type="number" name="subtotal" value="<?php echo $row['subtotal']; ?>"
                        class="form-control" id="subtotal" placeholder="Subtotal" required oninput="hitungTotal()">
                </div>
                <div class="form-floating mt-5">
                    <label for="pajak">Pajak</label>
                    <input type="number" name="pajak" value="<?php echo $row['pajak']; ?>"
                        class="form-control" id="pajak" placeholder="Pajak" required oninput="hitungTotal()">
                </div>
                <div class="form-floating mt-5">
                    <label for="total">Total</label>
                    <input type="number" name="total" value="<?php echo $row['total']; ?>"
                        class="form-control" id="total" placeholder="Total" required readonly>
                </div>
                <div class="form-floating mt-5">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" value="<?php echo $row['tanggal']; ?>"
                        class="form-control" id="tanggal" required>
                </div>
                <div class="form-floating mt-5">
                    <label for="waktu">Waktu</label>
                    <input type="time" step="1" name="waktu" value="<?php echo $row['waktu']; ?>"
                        class="form-control" id="waktu" required>
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

<script>
function hitungTotal() {
    var subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    var pajak = parseFloat(document.getElementById('pajak').value) || 0;
    document.getElementById('total').value = Math.round(subtotal + pajak);
}
</script>

<?php
    include ROOTPATH . '/include/footer.php';
?>

