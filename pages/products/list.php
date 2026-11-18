<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$daftar_produk = Produk::semua();
$count_semua = count($daftar_produk);
$count_kategori = [
    'Minuman'  => 0,
    'Tambahan' => 0,
    'Makanan'  => 0,
];
foreach ($daftar_produk as $prod_item) {
    $kat_item = trim((string)($prod_item['kategori'] ?? 'Minuman'));
    if ($kat_item === '' || !isset($count_kategori[$kat_item])) {
        $kat_item = 'Minuman';
    }
    $count_kategori[$kat_item]++;
}
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Produk</h1>
            <p class="page-subtitle">Kelola katalog menu minuman dan makanan Starbuck</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="search-box" style="width: 240px; max-width: 100%;">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="search-produk" class="form-control"
                    placeholder="Cari nama menu" autocomplete="off" aria-label="Pencarian menu">
            </div>
            <a href="/starbuck/pages/products/add.php" class="btn-coffee">+ Tambah Produk</a>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4" role="group" aria-label="Filter kategori produk">
        <button type="button" class="option-chip is-active" data-filter-kategori="Semua" aria-pressed="true">
            Semua (<span class="tabular-nums"><?php echo $count_semua; ?></span>)
        </button>
        <button type="button" class="option-chip" data-filter-kategori="Minuman" aria-pressed="false">
            Minuman (<span class="tabular-nums"><?php echo $count_kategori['Minuman']; ?></span>)
        </button>
        <button type="button" class="option-chip" data-filter-kategori="Tambahan" aria-pressed="false">
            Tambahan (<span class="tabular-nums"><?php echo $count_kategori['Tambahan']; ?></span>)
        </button>
        <button type="button" class="option-chip" data-filter-kategori="Makanan" aria-pressed="false">
            Makanan (<span class="tabular-nums"><?php echo $count_kategori['Makanan']; ?></span>)
        </button>
    </div>

    <?php if (empty($daftar_produk)): ?>
        <div class="card-soft empty-state">
            Belum ada produk yang terdaftar. Klik "+ Tambah Produk" untuk menambahkan menu pertama.
        </div>
    <?php else: ?>
        <div class="row g-4" id="produk-grid">
            <?php foreach ($daftar_produk as $row):
                $id_val       = $row['id_produk'] ?? $row['id'] ?? '';
                $nama_raw     = trim($row['nama_produk'] ?? '');
                $nama         = htmlspecialchars($nama_raw, ENT_QUOTES, 'UTF-8');
                $harga        = (float)($row['harga'] ?? 0);
                $kategori_raw = trim($row['kategori'] ?? 'Minuman');
                if ($kategori_raw === '') {
                    $kategori_raw = 'Minuman';
                }
                $kategori     = htmlspecialchars($kategori_raw, ENT_QUOTES, 'UTF-8');
                $badge_cls    = 'badge-category';
                if ($kategori_raw === 'Tambahan') {
                    $badge_cls .= ' badge-category-tambahan';
                } elseif ($kategori_raw === 'Makanan') {
                    $badge_cls .= ' badge-category-makanan';
                }
                $inisial      = strtoupper(substr($nama_raw !== '' ? $nama_raw : 'PR', 0, 2));
                $gambar       = trim($row['gambar'] ?? '');
                $has_img      = $gambar !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar));
            ?>
            <div class="col-md-6 col-xl-4 produk-item-col"
                 data-kategori="<?php echo $kategori; ?>"
                 data-nama-search="<?php echo htmlspecialchars(strtolower($nama_raw), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card-soft h-100 d-flex flex-column justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($has_img): ?>
                            <span class="product-thumb-frame">
                                <img src="../../assets/img/products/<?php echo rawurlencode(basename($gambar)); ?>"
                                     alt="<?php echo $nama; ?>"
                                     class="product-avatar-img"
                                     onerror="this.parentElement.classList.add('is-fallback'); this.classList.add('d-none'); if(this.nextElementSibling){this.nextElementSibling.classList.remove('d-none');}">
                                <span class="avatar-initial d-none" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                        <?php else: ?>
                            <span class="avatar-initial" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <div class="min-w-0 flex-grow-1">
                            <div class="mb-1">
                                <span class="<?php echo $badge_cls; ?>"><?php echo $kategori; ?></span>
                            </div>
                            <h2 class="h6 fw-semibold mb-1 text-truncate product-card-title"><?php echo $nama; ?></h2>
                            <div class="fw-bold product-price" style="color: var(--text-main); font-size: 16px;">
                                Rp <?php echo number_format($harga, 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-2 pt-3 border-top" style="border-color: var(--border-soft) !important;">
                        <a href="../transactions/add.php?id_produk=<?php echo urlencode((string)$id_val); ?>"
                           class="btn-coffee-outline btn-sm-soft">Pesan &rarr;</a>
                        <div class="d-inline-flex align-items-center gap-2">
                            <a href="edit.php?id=<?php echo urlencode((string)$id_val); ?>&id_produk=<?php echo urlencode((string)$id_val); ?>"
                               class="btn-neutral-outline btn-sm-soft">Edit</a>
                            <a href="/starbuck/process/process_products.php?action=delete&id=<?php echo urlencode((string)$id_val); ?>&id_produk=<?php echo urlencode((string)$id_val); ?>"
                               class="btn-soft-danger btn-sm-soft"
                               data-confirm-delete
                               data-confirm-message="Apakah Anda yakin ingin menghapus produk <?php echo $nama; ?>?">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card-soft empty-state d-none mt-3" id="produk-search-empty">
            Tidak ada produk yang cocok dengan filter atau pencarian Anda.
        </div>
    <?php endif; ?>

</div>

<script>
(function () {
    var searchInput = document.getElementById('search-produk');
    var filterChips = document.querySelectorAll('[data-filter-kategori]');
    var itemCols    = document.querySelectorAll('.produk-item-col');
    var emptyEl     = document.getElementById('produk-search-empty');
    var activeKat   = 'Semua';

    function applyFilter() {
        var keyword = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var visibleCount = 0;

        itemCols.forEach(function (col) {
            var nama = col.getAttribute('data-nama-search') || '';
            var kat  = col.getAttribute('data-kategori') || 'Minuman';
            var matchKat  = (activeKat === 'Semua' || kat === activeKat);
            var matchNama = (keyword === '' || nama.indexOf(keyword) !== -1);

            if (matchKat && matchNama) {
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
    }

    filterChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            activeKat = chip.getAttribute('data-filter-kategori') || 'Semua';
            filterChips.forEach(function (c) {
                var isMatch = (c === chip);
                c.classList.toggle('is-active', isMatch);
                c.setAttribute('aria-pressed', isMatch ? 'true' : 'false');
            });
            applyFilter();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
    }
})();
</script>

<?php
include ROOTPATH . '/include/footer.php';
?>
