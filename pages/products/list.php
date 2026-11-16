<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$query = "SELECT * FROM produk";
$result = mysqli_query($koneksi, $query);
$daftar_produk = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daftar_produk[] = $row;
    }
}
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Produk</h1>
            <p class="page-subtitle">Kelola katalog menu minuman dan makanan Starbuck</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="search-box flex-grow-1" style="min-width: 220px;">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="search-produk" class="form-control"
                    placeholder="Pencarian menu..." autocomplete="off" aria-label="Pencarian menu">
            </div>
            <a href="/starbuck/pages/products/add.php" class="btn-coffee">+ Tambah Produk</a>
        </div>
    </div>

    <?php if (empty($daftar_produk)): ?>
        <div class="card-soft empty-state">
            Belum ada produk yang terdaftar. Klik "+ Tambah Produk" untuk menambahkan menu pertama.
        </div>
    <?php else: ?>
        <div class="row g-4" id="produk-grid">
            <?php foreach ($daftar_produk as $row):
                $id_val   = $row['id_produk'] ?? $row['id'] ?? '';
                $nama_raw = trim($row['nama_produk'] ?? '');
                $nama     = htmlspecialchars($nama_raw, ENT_QUOTES, 'UTF-8');
                $harga    = (float)($row['harga'] ?? 0);
                $inisial  = strtoupper(substr($nama_raw !== '' ? $nama_raw : 'PR', 0, 2));
                $gambar   = trim($row['gambar'] ?? '');
                $has_img  = $gambar !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar));
            ?>
            <div class="col-md-6 col-xl-4 produk-item-col"
                 data-nama-search="<?php echo htmlspecialchars(strtolower($nama_raw), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card-soft h-100 d-flex flex-column justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($has_img): ?>
                            <img src="../../assets/img/products/<?php echo rawurlencode(basename($gambar)); ?>"
                                 alt="<?php echo $nama; ?>"
                                 class="product-avatar-img">
                        <?php else: ?>
                            <span class="avatar-initial" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <div class="min-w-0 flex-grow-1">
                            <h2 class="h6 fw-semibold mb-1 text-truncate"><?php echo $nama; ?></h2>
                            <div class="fw-bold tabular-nums" style="color: var(--primary-coffee); font-size: 16px;">
                                Rp <?php echo number_format($harga, 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--border-soft) !important;">
                        <a href="edit.php?id=<?php echo urlencode($id_val); ?>&id_produk=<?php echo urlencode($id_val); ?>"
                           class="btn-neutral-outline btn-sm-soft">Edit</a>
                        <a href="/starbuck/process/process_products.php?action=delete&id=<?php echo urlencode($id_val); ?>&id_produk=<?php echo urlencode($id_val); ?>"
                           class="btn-soft-danger btn-sm-soft"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card-soft empty-state d-none mt-3" id="produk-search-empty">
            Tidak ada produk yang cocok dengan kata kunci pencarian Anda.
        </div>
    <?php endif; ?>

</div>

<script>
(function () {
    var searchInput = document.getElementById('search-produk');
    var itemCols    = document.querySelectorAll('.produk-item-col');
    var emptyEl     = document.getElementById('produk-search-empty');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var keyword = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;

            itemCols.forEach(function (col) {
                var nama = col.getAttribute('data-nama-search') || '';
                if (keyword === '' || nama.indexOf(keyword) !== -1) {
                    col.classList.remove('d-none');
                    visibleCount++;
                } else {
                    col.classList.add('d-none');
                }
            });

            if (emptyEl) {
                if (visibleCount === 0 && itemCols.length > 0) {
                    emptyEl.classList.remove('d-none');
                } else {
                    emptyEl.classList.add('d-none');
                }
            }
        });
    }
})();
</script>

<?php
include ROOTPATH . '/include/footer.php';
?>
