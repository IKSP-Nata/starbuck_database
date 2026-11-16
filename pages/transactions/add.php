<?php
    define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
    include ROOTPATH . '/include/header.php';
    include ROOTPATH . '/config/config.php';

    $query_produk  = "SELECT * FROM produk ORDER BY nama_produk ASC";
    $result_produk = mysqli_query($koneksi, $query_produk);
?>

<style>
.text-light {
    color: #f8f9fa !important;
}
.form-select {
    display: block;
    width: 100%;
    padding: 0.55rem 2.25rem 0.55rem 0.85rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: var(--bs-control-fg, #f8f9fa);
    background-color: var(--bs-control-bg, var(--bs-bg-1, #212529));
    background-image: var(--bs-control-select-bg-dark, url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff80' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e"));
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    border: var(--bs-control-border-width, 1px) solid var(--bs-control-border-color, #495057);
    border-radius: var(--bs-control-border-radius, 0.375rem);
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
.form-select:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.form-select option {
    background-color: #212529;
    color: #f8f9fa;
}
</style>

<div class="container">
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6 mt-5">
            <div class="card bg-1 border-secondary p-4">
                <h4 class="mb-4">Tambah Transaksi</h4>
                <form action="../../process/process_transactions.php" method="post">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label for="id_produk" class="form-label text-light">Pilih Menu</label>
                        <select name="id_produk" id="id_produk" class="form-select" required onchange="hitungTotal()">
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
                    </div>

                    <div class="mb-3">
                        <label for="jumlah" class="form-label text-light">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" id="jumlah"
                            placeholder="Jumlah" min="1" value="1" required oninput="hitungTotal()">
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



