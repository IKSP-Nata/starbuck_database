<?php
    define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
    include ROOTPATH . '/include/header.php';
    include ROOTPATH . '/config/config.php';

    $query_produk  = "SELECT * FROM produk ORDER BY nama_produk ASC";
    $result_produk = mysqli_query($koneksi, $query_produk);

    $daftar_produk = [];
    if ($result_produk) {
        while ($row = mysqli_fetch_assoc($result_produk)) {
            $daftar_produk[] = $row;
        }
    }
    $jumlah_menu = count($daftar_produk);
?>

<div class="container-fluid px-0">
    <form action="../../process/process_transactions.php" method="post" id="form-transaksi">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id_produk" id="id_produk" value="" required>

        <div class="row g-4 align-items-start">

            <!-- Kolom Kiri: Katalog Pilih Menu (PRD 4.5) -->
            <div class="col-lg-7">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
                    <div>
                        <h1 class="page-title">Pilih Menu</h1>
                        <p class="page-subtitle">Klik salah satu kartu menu untuk memasukkan ke tagihan</p>
                    </div>
                    <span class=" page-subtitle tabular-nums" id="menu-count">
                        <?php echo $jumlah_menu; ?> menu tersedia
                    </span>
                </div>

                <div class="search-box mb-4">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="search-menu" class="form-control"
                        placeholder="Cari nama menu..." autocomplete="off" aria-label="Cari nama menu">
                </div>

                <?php if (empty($daftar_produk)): ?>
                    <div class="card-soft empty-state">
                        Belum ada produk di katalog. Silakan tambahkan menu di halaman Produk terlebih dahulu.
                    </div>
                <?php else: ?>
                    <div class="row g-3" id="menu-grid">
                        <?php foreach ($daftar_produk as $produk):
                            $id_prod    = $produk['id_produk'] ?? $produk['id'] ?? '';
                            $nama_raw   = trim($produk['nama_produk'] ?? '');
                            $nama_prod  = htmlspecialchars($nama_raw, ENT_QUOTES, 'UTF-8');
                            $harga_prod = (float)($produk['harga'] ?? 0);
                            $inisial    = strtoupper(substr($nama_raw !== '' ? $nama_raw : 'MN', 0, 2));
                            $gambar     = trim($produk['gambar'] ?? '');
                            $has_img    = $gambar !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar));
                            $img_src    = $has_img ? '../../assets/img/products/' . rawurlencode(basename($gambar)) : '';
                        ?>
                        <div class="col-md-6 menu-item-col" data-nama-search="<?php echo htmlspecialchars(strtolower($nama_raw), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="button"
                                class="menu-card"
                                data-id="<?php echo htmlspecialchars($id_prod, ENT_QUOTES, 'UTF-8'); ?>"
                                data-nama="<?php echo $nama_prod; ?>"
                                data-harga="<?php echo $harga_prod; ?>"
                                data-inisial="<?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?>"
                                data-gambar="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>"
                                aria-pressed="false">
                                <?php if ($has_img): ?>
                                    <img src="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo $nama_prod; ?>"
                                         class="product-avatar-img">
                                <?php else: ?>
                                    <span class="avatar-initial" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <span class="menu-card-body">
                                    <span class="menu-card-title d-block"><?php echo $nama_prod; ?></span>
                                    <span class="menu-card-price d-block">Rp <?php echo number_format($harga_prod, 0, ',', '.'); ?></span>
                                </span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card-soft empty-state d-none mt-3" id="search-empty">
                        Tidak ada menu yang cocok dengan pencarian Anda.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan: Panel Sticky Bills (PRD 4.5) -->
            <div class="col-lg-5">
                <div class="card-soft bills-panel">
                    <div class="mb-3">
                        <h2 class="h4 fw-semibold mb-1">Bills</h2>
                        <p class="page-subtitle mb-0">Ringkasan Transaksi</p>
                    </div>

                    <!-- Status kosong saat belum ada menu dipilih (PRD 3.2) -->
                    <div class="empty-state bills-selected-item mb-3" id="bills-empty">
                        Pilih menu di sebelah kiri untuk memulai transaksi.
                    </div>

                    <!-- Detail item terpilih + kontrol jumlah -->
                    <div class="bills-selected-item mb-3 d-none" id="bills-item">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="avatar-initial" id="selected-avatar" aria-hidden="true">--</span>
                            <img src="" alt="" class="product-avatar-img d-none" id="selected-avatar-img">
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate" id="selected-nama">-</div>
                                <div class="page-subtitle tabular-nums mt-0" id="selected-harga">Rp 0</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: var(--border-soft) !important;">
                            <label for="jumlah" class="form-label mb-0">Jumlah Beli</label>
                            <div class="qty-control">
                                <button type="button" class="qty-btn" id="btn-qty-minus" aria-label="Kurangi jumlah">&minus;</button>
                                <input type="number" name="jumlah" id="jumlah" class="form-control qty-input"
                                    min="1" value="1" required>
                                <button type="button" class="qty-btn" id="btn-qty-plus" aria-label="Tambah jumlah">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Subtotal, Pajak 11%, dan Total -->
                    <div class="pt-1">
                        <div class="bills-row">
                            <span class="text-muted">Subtotal</span>
                            <span id="summary-subtotal" class="fw-semibold tabular-nums">Rp 0</span>
                        </div>
                        <div class="bills-row">
                            <span class="text-muted">Pajak (11%)</span>
                            <span id="summary-pajak" class="fw-semibold tabular-nums">Rp 0</span>
                        </div>

                        <hr class="dashed-divider">

                        <div class="bills-total-row">
                            <span class="bills-total-label">Total</span>
                            <span id="summary-total" class="bills-total-value">Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-4 d-grid gap-2">
                        <button type="submit" id="btn-submit" class="btn-coffee btn-block" disabled>
                            Simpan &amp; Cetak Struk
                        </button>
                        <a href="list.php" class="btn-coffee-outline btn-block">Kembali ke Riwayat</a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
(function () {
    var selectedHarga = 0;
    var inputIdProduk = document.getElementById('id_produk');
    var inputJumlah   = document.getElementById('jumlah');
    var btnMinus      = document.getElementById('btn-qty-minus');
    var btnPlus       = document.getElementById('btn-qty-plus');
    var btnSubmit     = document.getElementById('btn-submit');

    var billsEmpty    = document.getElementById('bills-empty');
    var billsItem     = document.getElementById('bills-item');
    var selectedAvatar = document.getElementById('selected-avatar');
    var selectedAvatarImg = document.getElementById('selected-avatar-img');
    var selectedNama  = document.getElementById('selected-nama');
    var selectedHargaEl = document.getElementById('selected-harga');

    var elSubtotal    = document.getElementById('summary-subtotal');
    var elPajak       = document.getElementById('summary-pajak');
    var elTotal       = document.getElementById('summary-total');

    var menuCards     = document.querySelectorAll('.menu-card');
    var searchInput   = document.getElementById('search-menu');
    var menuCols      = document.querySelectorAll('.menu-item-col');
    var menuCountEl   = document.getElementById('menu-count');
    var searchEmptyEl = document.getElementById('search-empty');

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungTotal() {
        var hasProduct = inputIdProduk.value.trim() !== '';
        var jumlah = parseInt(inputJumlah.value, 10);

        if (isNaN(jumlah) || jumlah < 1) {
            jumlah = 0;
        }

        var subtotal = hasProduct ? (selectedHarga * jumlah) : 0;
        var pajak    = subtotal * 0.11;
        var total    = subtotal + pajak;

        elSubtotal.textContent = formatRupiah(subtotal);
        elPajak.textContent    = formatRupiah(pajak);
        elTotal.textContent    = formatRupiah(total);

        btnSubmit.disabled = !(hasProduct && jumlah >= 1);
    }

    function pilihMenu(card) {
        menuCards.forEach(function (c) {
            c.classList.remove('is-selected');
            c.setAttribute('aria-pressed', 'false');
        });

        card.classList.add('is-selected');
        card.setAttribute('aria-pressed', 'true');

        var id      = card.getAttribute('data-id') || '';
        var nama    = card.getAttribute('data-nama') || '';
        var harga   = parseFloat(card.getAttribute('data-harga')) || 0;
        var inisial = card.getAttribute('data-inisial') || 'MN';
        var gambar  = card.getAttribute('data-gambar') || '';

        selectedHarga = harga;
        inputIdProduk.value = id;

        if (!inputJumlah.value || parseInt(inputJumlah.value, 10) < 1) {
            inputJumlah.value = 1;
        }

        if (gambar !== '') {
            selectedAvatarImg.src = gambar;
            selectedAvatarImg.alt = nama;
            selectedAvatarImg.classList.remove('d-none');
            selectedAvatar.classList.add('d-none');
        } else {
            selectedAvatarImg.src = '';
            selectedAvatarImg.classList.add('d-none');
            selectedAvatar.textContent = inisial;
            selectedAvatar.classList.remove('d-none');
        }
        selectedNama.textContent    = nama;
        selectedHargaEl.textContent = formatRupiah(harga) + ' / item';

        billsEmpty.classList.add('d-none');
        billsItem.classList.remove('d-none');

        hitungTotal();
    }

    menuCards.forEach(function (card) {
        card.addEventListener('click', function () {
            pilihMenu(card);
        });
    });

    if (btnMinus) {
        btnMinus.addEventListener('click', function () {
            var current = parseInt(inputJumlah.value, 10) || 1;
            if (current > 1) {
                inputJumlah.value = current - 1;
                hitungTotal();
            }
        });
    }

    if (btnPlus) {
        btnPlus.addEventListener('click', function () {
            var current = parseInt(inputJumlah.value, 10) || 0;
            inputJumlah.value = current + 1;
            hitungTotal();
        });
    }

    if (inputJumlah) {
        inputJumlah.addEventListener('input', hitungTotal);
        inputJumlah.addEventListener('change', function () {
            var val = parseInt(inputJumlah.value, 10);
            if (isNaN(val) || val < 1) {
                inputJumlah.value = 1;
            }
            hitungTotal();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var keyword = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;

            menuCols.forEach(function (col) {
                var namaMenu = col.getAttribute('data-nama-search') || '';
                if (keyword === '' || namaMenu.indexOf(keyword) !== -1) {
                    col.classList.remove('d-none');
                    visibleCount++;
                } else {
                    col.classList.add('d-none');
                }
            });

            if (menuCountEl) {
                menuCountEl.textContent = visibleCount + ' menu tersedia';
            }

            if (searchEmptyEl) {
                if (visibleCount === 0 && menuCols.length > 0) {
                    searchEmptyEl.classList.remove('d-none');
                } else {
                    searchEmptyEl.classList.add('d-none');
                }
            }
        });
    }

    hitungTotal();
})();
</script>

<?php
    include ROOTPATH . '/include/footer.php';
?>



