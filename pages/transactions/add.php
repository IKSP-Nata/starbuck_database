<?php
    define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
    include ROOTPATH . '/include/header.php';
    include ROOTPATH . '/config/config.php';

    $query_produk  = "SELECT * FROM produk ORDER BY nama_produk ASC";
    $result_produk = mysqli_query($koneksi, $query_produk);
?>

<div class="container">
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6 mt-5">
            <div class="card bg-1 border-secondary p-4">
                <h4 class="mb-4">Tambah Transaksi</h4>
                <form action="../../process/process_transactions.php" method="post">
                    <input type="hidden" name="action" value="add">

                    <div class="form-floating mt-3">
                        <select name="id_produk" id="id_produk" class="form-control" required onchange="hitungTotal()">
                            <option value="" data-harga="0">-- Pilih Menu --</option>
                            <?php
                            if ($result_produk) {
                                while ($produk = mysqli_fetch_assoc($result_produk)) {
                                    $id_prod = $produk['id_produk'] ?? $produk['id'] ?? '';
                                    $nama_prod = htmlspecialchars($produk['nama_produk'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $harga_prod = (float)($produk['harga'] ?? 0);
                            ?>
                            <option value="<?php echo htmlspecialchars($id_prod, ENT_QUOTES, 'UTF-8'); ?>" data-harga="<?php echo $harga_prod; ?>">
                                <?php echo $nama_prod . ' - Rp ' . number_format($harga_prod, 0, ',', '.'); ?>
                            </option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                        <label for="id_produk">Pilih Menu</label>
                    </div>

                    <div class="form-floating mt-4">
                        <input type="number" name="jumlah" class="form-control" id="jumlah"
                            placeholder="Jumlah" min="1" value="1" required oninput="hitungTotal()">
                        <label for="jumlah">Jumlah</label>
                    </div>

                    <div class="card bg-1 border-secondary mt-4 p-3">
                        <div class="text-muted mb-2" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:.05em;">
                            Ringkasan Pembayaran
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted">Subtotal</span>
                            <span id="summary-subtotal" class="fw-bold">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted">Pajak (11%)</span>
                            <span id="summary-pajak" class="fw-bold">Rp 0</span>
                        </div>
                        <hr class="my-2 border-secondary">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="fw-bold">Total</span>
                            <span id="summary-total" class="fw-bold" style="font-size:1.15rem;">Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn-solid theme-success">Simpan Transaksi</button>
                        <a href="list.php" class="btn-solid theme-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-3"></div>
    </div>
</div>

<script>
function formatRupiah(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
}

function hitungTotal() {
    var selectMenu = document.getElementById('id_produk');
    var selectedOption = selectMenu.options[selectMenu.selectedIndex];
    var harga = parseFloat(selectedOption ? selectedOption.getAttribute('data-harga') : 0) || 0;
    var jumlahInput = document.getElementById('jumlah');
    var jumlah = parseInt(jumlahInput.value, 10) || 0;

    if (jumlah < 0) {
        jumlah = 0;
    }

    var subtotal = harga * jumlah;
    var pajak = subtotal * 0.11;
    var total = subtotal + pajak;

    document.getElementById('summary-subtotal').textContent = formatRupiah(subtotal);
    document.getElementById('summary-pajak').textContent = formatRupiah(pajak);
    document.getElementById('summary-total').textContent = formatRupiah(total);
}

document.addEventListener('DOMContentLoaded', hitungTotal);
</script>

<?php
    include ROOTPATH . '/include/footer.php';
?>


