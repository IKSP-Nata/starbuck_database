<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$daftar_produk = Produk::semua('nama');
$jumlah_menu   = count($daftar_produk);
$daftar_kasir  = Kasir::semua('nama');
?>

<div class="container-fluid px-0">
    <form action="../../process/process_transactions.php" method="post" id="form-transaksi">
        <input type="hidden" name="action" value="add">
        <div id="cart-hidden-inputs"></div>

        <div class="row g-4 align-items-start">

            <div class="col-lg-7">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
                    <div>
                        <h1 class="page-title">Pilih Menu</h1>
                        <p class="page-subtitle">Klik salah satu kartu menu untuk memasukkan ke tagihan</p>
                    </div>
                    <span class="page-subtitle tabular-nums" id="menu-count">
                        <?php echo $jumlah_menu; ?> menu tersedia
                    </span>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                    <div class="d-flex flex-wrap align-items-center gap-2" role="group" aria-label="Filter kategori menu">
                        <button type="button" class="option-chip is-active" data-filter-menu="Semua" aria-pressed="true">Semua</button>
                        <button type="button" class="option-chip" data-filter-menu="Minuman" aria-pressed="false">Minuman</button>
                        <button type="button" class="option-chip" data-filter-menu="Tambahan" aria-pressed="false">Tambahan</button>
                        <button type="button" class="option-chip" data-filter-menu="Makanan" aria-pressed="false">Makanan</button>
                    </div>
                    <div class="search-box" style="flex: 1 1 200px; max-width: 260px;">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" id="search-menu" class="form-control"
                            placeholder="Cari nama menu" autocomplete="off" aria-label="Cari nama menu">
                    </div>
                </div>

                <?php if (empty($daftar_produk)): ?>
                    <div class="card-soft empty-state">
                        Belum ada produk di katalog. Silakan tambahkan menu di halaman Produk terlebih dahulu.
                    </div>
                <?php else: ?>
                    <div class="row g-3" id="menu-grid">
                        <?php foreach ($daftar_produk as $produk):
                            $id_prod      = $produk['id_produk'] ?? $produk['id'] ?? '';
                            $nama_raw     = trim($produk['nama_produk'] ?? '');
                            $nama_prod    = htmlspecialchars($nama_raw, ENT_QUOTES, 'UTF-8');
                            $harga_prod   = (float)($produk['harga'] ?? 0);
                            $kategori_raw = trim($produk['kategori'] ?? 'Minuman');
                            if ($kategori_raw === '') {
                                $kategori_raw = 'Minuman';
                            }
                            $kategori_esc = htmlspecialchars($kategori_raw, ENT_QUOTES, 'UTF-8');
                            $badge_cls    = 'badge-category';
                            if ($kategori_raw === 'Tambahan') {
                                $badge_cls .= ' badge-category-tambahan';
                            } elseif ($kategori_raw === 'Makanan') {
                                $badge_cls .= ' badge-category-makanan';
                            }
                            $inisial      = strtoupper(substr($nama_raw !== '' ? $nama_raw : 'MN', 0, 2));
                            $gambar       = trim($produk['gambar'] ?? '');
                            $has_img      = $gambar !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar));
                            $img_src      = $has_img ? '../../assets/img/products/' . rawurlencode(basename($gambar)) : '';
                        ?>
                        <div class="col-md-6 menu-item-col"
                             data-kategori="<?php echo $kategori_esc; ?>"
                             data-nama-search="<?php echo htmlspecialchars(strtolower($nama_raw), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="button"
                                class="menu-card"
                                data-id="<?php echo htmlspecialchars((string)$id_prod, ENT_QUOTES, 'UTF-8'); ?>"
                                data-nama="<?php echo $nama_prod; ?>"
                                data-harga="<?php echo $harga_prod; ?>"
                                data-kategori="<?php echo $kategori_esc; ?>"
                                data-inisial="<?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?>"
                                data-gambar="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>"
                                aria-pressed="false">
                                <span class="menu-card-check" aria-hidden="true">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <?php if ($has_img): ?>
                                    <span class="product-thumb-frame">
                                        <img src="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>"
                                             alt="<?php echo $nama_prod; ?>"
                                             class="product-avatar-img"
                                             onerror="this.parentElement.classList.add('is-fallback'); this.classList.add('d-none'); if(this.nextElementSibling){this.nextElementSibling.classList.remove('d-none');}">
                                        <span class="avatar-initial d-none" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="avatar-initial" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <span class="menu-card-body">
                                    <span class="<?php echo $badge_cls; ?> mb-1"><?php echo $kategori_esc; ?></span>
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

            <div class="col-lg-5">
                <div class="card-soft bills-panel">
                    <div class="mb-3">
                        <h2 class="h4 fw-semibold mb-1">Bills</h2>
                        <p class="page-subtitle mb-0">Ringkasan Transaksi</p>
                    </div>

                    <div class="mb-3">
                        <label for="id_kasir" class="form-label fw-medium">Kasir Bertugas</label>
                        <select name="id_kasir" id="id_kasir" class="form-select" required>
                            <option value="">Pilih kasir bertugas</option>
                            <?php foreach ($daftar_kasir as $kasir): ?>
                                <option value="<?php echo (int)$kasir['id_kasir']; ?>">
                                    <?php echo htmlspecialchars($kasir['nama_kasir'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bills-selected-item mb-3 d-none" id="bills-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="label-caps">Atur Item Terpilih</span>
                            <button type="button" class="btn-close-config" id="btn-close-config" aria-label="Tutup pengaturan item">&times;</button>
                        </div>

                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="avatar-initial" id="selected-avatar" aria-hidden="true">--</span>
                            <img src="" alt="" class="product-avatar-img d-none" id="selected-avatar-img">
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate product-card-title" id="selected-nama">-</div>
                                <div class="page-subtitle tabular-nums mt-0" id="selected-harga">Rp 0</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: var(--border-soft) !important;">
                            <label for="jumlah_item" class="form-label mb-0">Jumlah Item</label>
                            <div class="qty-control">
                                <button type="button" class="qty-btn" id="btn-qty-minus" aria-label="Kurangi jumlah">&minus;</button>
                                <input type="number" id="jumlah_item" class="form-control qty-input"
                                    min="1" value="1">
                                <button type="button" class="qty-btn" id="btn-qty-plus" aria-label="Tambah jumlah">+</button>
                            </div>
                        </div>

                        <div class="drink-custom-collapse" id="drink-custom-wrap" aria-hidden="true">
                            <div class="drink-custom-inner">
                                <div class="drink-custom-section mb-0 pb-0">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium mb-2">Ukuran Gelas</label>
                                        <div class="option-chip-group" role="group" aria-label="Pilihan ukuran gelas">
                                            <button type="button" class="option-chip" data-ukuran="Small" aria-pressed="false" tabindex="-1">Small</button>
                                            <button type="button" class="option-chip is-active" data-ukuran="Medium" aria-pressed="true" tabindex="-1">Medium</button>
                                            <button type="button" class="option-chip" data-ukuran="Large" aria-pressed="false" tabindex="-1">Large</button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-medium mb-2">Penyajian</label>
                                        <div class="option-chip-group" role="group" aria-label="Pilihan penyajian suhu">
                                            <button type="button" class="option-chip is-active" data-suhu="Ice" aria-pressed="true" tabindex="-1">Ice</button>
                                            <button type="button" class="option-chip" data-suhu="Hot" aria-pressed="false" tabindex="-1">Hot</button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label fw-medium mb-2">Tingkat Gula</label>
                                        <div class="option-chip-group" role="group" aria-label="Pilihan tingkat gula">
                                            <button type="button" class="option-chip" data-gula="No sugar" aria-pressed="false" tabindex="-1">No sugar</button>
                                            <button type="button" class="option-chip" data-gula="Less sugar" aria-pressed="false" tabindex="-1">Less sugar</button>
                                            <button type="button" class="option-chip is-active" data-gula="Normal" aria-pressed="true" tabindex="-1">Normal</button>
                                            <button type="button" class="option-chip" data-gula="High sugar" aria-pressed="false" tabindex="-1">High sugar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-2">
                            <button type="button" id="btn-add-cart" class="btn-coffee btn-block">
                                + Tambah ke Keranjang
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="form-label fw-semibold mb-0">Daftar Pesanan</span>
                            <span class="badge-category tabular-nums" id="cart-count-badge">0 item</span>
                        </div>

                        <div class="empty-state bills-selected-item" id="cart-empty">
                            Keranjang masih kosong. Klik menu di sebelah kiri lalu tekan "+ Tambah ke Keranjang".
                        </div>

                        <div class="cart-list d-none" id="cart-list"></div>
                    </div>

                    <div class="pt-1" aria-live="polite" aria-atomic="true">
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
    var KASIR_STORAGE_KEY = 'starbuck_active_kasir';

    var currentProduct   = null;
    var currentUkuran    = 'Medium';
    var currentSuhu      = 'Ice';
    var currentGula      = 'Normal';
    var activeFilterKat  = 'Semua';
    var cartItems        = [];

    var selectKasir       = document.getElementById('id_kasir');
    var inputJumlah       = document.getElementById('jumlah_item');
    var btnMinus          = document.getElementById('btn-qty-minus');
    var btnPlus           = document.getElementById('btn-qty-plus');
    var btnAddCart        = document.getElementById('btn-add-cart');
    var btnCloseConfig    = document.getElementById('btn-close-config');
    var btnSubmit         = document.getElementById('btn-submit');

    var billsItem         = document.getElementById('bills-item');
    var selectedAvatar    = document.getElementById('selected-avatar');
    var selectedAvatarImg = document.getElementById('selected-avatar-img');
    var selectedNama      = document.getElementById('selected-nama');
    var selectedHargaEl   = document.getElementById('selected-harga');

    var cartEmptyEl       = document.getElementById('cart-empty');
    var cartListEl        = document.getElementById('cart-list');
    var cartCountBadge    = document.getElementById('cart-count-badge');
    var cartHiddenInputs  = document.getElementById('cart-hidden-inputs');

    var drinkCustomWrap   = document.getElementById('drink-custom-wrap');
    var ukuranChips       = document.querySelectorAll('.option-chip[data-ukuran]');
    var suhuChips         = document.querySelectorAll('.option-chip[data-suhu]');
    var gulaChips         = document.querySelectorAll('.option-chip[data-gula]');
    var filterMenuChips   = document.querySelectorAll('[data-filter-menu]');

    var elSubtotal        = document.getElementById('summary-subtotal');
    var elPajak           = document.getElementById('summary-pajak');
    var elTotal           = document.getElementById('summary-total');

    var menuCards         = document.querySelectorAll('.menu-card');
    var searchInput       = document.getElementById('search-menu');
    var menuCols          = document.querySelectorAll('.menu-item-col');
    var menuCountEl       = document.getElementById('menu-count');
    var searchEmptyEl     = document.getElementById('search-empty');

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    if (selectKasir) {
        try {
            var savedKasir = localStorage.getItem(KASIR_STORAGE_KEY);
            if (savedKasir) {
                var optionExists = Array.prototype.some.call(selectKasir.options, function (opt) {
                    return opt.value === savedKasir;
                });
                if (optionExists) {
                    selectKasir.value = savedKasir;
                }
            }
        } catch (e) {}

        selectKasir.addEventListener('change', function () {
            try {
                if (selectKasir.value) {
                    localStorage.setItem(KASIR_STORAGE_KEY, selectKasir.value);
                }
            } catch (e) {}
            hitungTotal();
        });
    }

    function getActiveCatatan() {
        if (currentProduct && currentProduct.kategori === 'Minuman') {
            return currentSuhu + ', ' + currentUkuran + ', ' + currentGula;
        }
        return '';
    }

    function setActiveChip(chips, attrName, targetValue) {
        chips.forEach(function (chip) {
            var isMatch = chip.getAttribute(attrName) === targetValue;
            chip.classList.toggle('is-active', isMatch);
            chip.setAttribute('aria-pressed', isMatch ? 'true' : 'false');
        });
    }

    function getDefaultUkuran(namaMenu) {
        var n = String(namaMenu || '');
        if (/tall/i.test(n)) {
            return 'Small';
        }
        if (/grande/i.test(n)) {
            return 'Medium';
        }
        if (/venti/i.test(n)) {
            return 'Large';
        }
        return 'Medium';
    }

    function toggleDrinkCustomization(isMinuman, namaMenu) {
        if (!drinkCustomWrap) {
            return;
        }
        var allChips = drinkCustomWrap.querySelectorAll('.option-chip');

        if (isMinuman) {
            currentUkuran = getDefaultUkuran(namaMenu);
            currentSuhu   = 'Ice';
            var isBlackCoffee = /americano|espresso|pike\s*place/i.test(namaMenu || '');
            currentGula = isBlackCoffee ? 'No sugar' : 'Normal';

            setActiveChip(ukuranChips, 'data-ukuran', currentUkuran);
            setActiveChip(suhuChips, 'data-suhu', currentSuhu);
            setActiveChip(gulaChips, 'data-gula', currentGula);

            drinkCustomWrap.classList.add('is-open');
            drinkCustomWrap.setAttribute('aria-hidden', 'false');
            allChips.forEach(function (chip) {
                chip.removeAttribute('tabindex');
            });
        } else {
            drinkCustomWrap.classList.remove('is-open');
            drinkCustomWrap.setAttribute('aria-hidden', 'true');
            allChips.forEach(function (chip) {
                chip.setAttribute('tabindex', '-1');
            });
        }
    }

    ukuranChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var val = chip.getAttribute('data-ukuran');
            if (val) {
                currentUkuran = val;
                setActiveChip(ukuranChips, 'data-ukuran', currentUkuran);
            }
        });
    });

    suhuChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var val = chip.getAttribute('data-suhu');
            if (val) {
                currentSuhu = val;
                setActiveChip(suhuChips, 'data-suhu', currentSuhu);
            }
        });
    });

    gulaChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var val = chip.getAttribute('data-gula');
            if (val) {
                currentGula = val;
                setActiveChip(gulaChips, 'data-gula', currentGula);
            }
        });
    });

    function syncMenuCardChecks() {
        var inCartIds = {};
        cartItems.forEach(function (it) {
            inCartIds[String(it.id_produk)] = true;
        });

        menuCards.forEach(function (c) {
            var cid = String(c.getAttribute('data-id') || '');
            var isCurrent = currentProduct && String(currentProduct.id) === cid;
            var isInCart  = Boolean(inCartIds[cid]);
            c.classList.toggle('is-selected', Boolean(isCurrent || isInCart));
            c.setAttribute('aria-pressed', (isCurrent || isInCart) ? 'true' : 'false');
        });
    }

    function renderCart() {
        if (!cartListEl || !cartHiddenInputs) {
            return;
        }

        cartListEl.innerHTML = '';
        cartHiddenInputs.innerHTML = '';

        var totalQty = 0;

        if (cartItems.length === 0) {
            cartListEl.classList.add('d-none');
            if (cartEmptyEl) cartEmptyEl.classList.remove('d-none');
        } else {
            if (cartEmptyEl) cartEmptyEl.classList.add('d-none');
            cartListEl.classList.remove('d-none');

            cartItems.forEach(function (item, idx) {
                totalQty += item.kuantitas;
                var lineSubtotal = Math.round(item.harga * item.kuantitas);

                var inId = document.createElement('input');
                inId.type = 'hidden';
                inId.name = 'items[' + idx + '][id_produk]';
                inId.value = item.id_produk;
                cartHiddenInputs.appendChild(inId);

                var inQty = document.createElement('input');
                inQty.type = 'hidden';
                inQty.name = 'items[' + idx + '][kuantitas]';
                inQty.value = item.kuantitas;
                cartHiddenInputs.appendChild(inQty);

                var inNote = document.createElement('input');
                inNote.type = 'hidden';
                inNote.name = 'items[' + idx + '][catatan]';
                inNote.value = item.catatan;
                cartHiddenInputs.appendChild(inNote);

                var row = document.createElement('div');
                row.className = 'cart-item-row';

                var topLine = document.createElement('div');
                topLine.className = 'd-flex align-items-start justify-content-between gap-2';

                var infoWrap = document.createElement('div');
                infoWrap.className = 'min-w-0 flex-grow-1';

                var titleEl = document.createElement('div');
                titleEl.className = 'fw-semibold product-card-title text-truncate';
                titleEl.textContent = item.nama;
                infoWrap.appendChild(titleEl);

                if (item.catatan !== '') {
                    var badgeWrap = document.createElement('div');
                    badgeWrap.className = 'mt-1';
                    var badgeEl = document.createElement('span');
                    badgeEl.className = 'badge-category';
                    badgeEl.textContent = item.catatan;
                    badgeWrap.appendChild(badgeEl);
                    infoWrap.appendChild(badgeWrap);
                }

                var priceEachEl = document.createElement('div');
                priceEachEl.className = 'page-subtitle tabular-nums mt-1';
                priceEachEl.textContent = formatRupiah(item.harga) + ' / item';
                infoWrap.appendChild(priceEachEl);

                var delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'btn-cart-remove';
                delBtn.setAttribute('aria-label', 'Hapus ' + item.nama + ' dari keranjang');
                delBtn.innerHTML = '<i class="bi bi-trash3" aria-hidden="true"></i>';
                delBtn.addEventListener('click', function () {
                    cartItems.splice(idx, 1);
                    renderCart();
                    syncMenuCardChecks();
                    hitungTotal();
                });

                topLine.appendChild(infoWrap);
                topLine.appendChild(delBtn);

                var bottomLine = document.createElement('div');
                bottomLine.className = 'd-flex align-items-center justify-content-between gap-2 mt-2 pt-2 border-top';
                bottomLine.style.borderColor = 'var(--border-soft)';

                var qtyCtrl = document.createElement('div');
                qtyCtrl.className = 'qty-control';

                var minusB = document.createElement('button');
                minusB.type = 'button';
                minusB.className = 'qty-btn qty-btn-sm';
                minusB.setAttribute('aria-label', 'Kurangi jumlah ' + item.nama);
                minusB.innerHTML = '&minus;';
                minusB.addEventListener('click', function () {
                    if (item.kuantitas > 1) {
                        item.kuantitas--;
                        renderCart();
                        hitungTotal();
                    }
                });

                var qtyIn = document.createElement('input');
                qtyIn.type = 'number';
                qtyIn.className = 'form-control qty-input qty-input-sm';
                qtyIn.min = '1';
                qtyIn.value = String(item.kuantitas);
                qtyIn.setAttribute('aria-label', 'Jumlah ' + item.nama);
                qtyIn.addEventListener('change', function () {
                    var v = parseInt(qtyIn.value, 10);
                    item.kuantitas = (isNaN(v) || v < 1) ? 1 : v;
                    renderCart();
                    hitungTotal();
                });

                var plusB = document.createElement('button');
                plusB.type = 'button';
                plusB.className = 'qty-btn qty-btn-sm';
                plusB.setAttribute('aria-label', 'Tambah jumlah ' + item.nama);
                plusB.textContent = '+';
                plusB.addEventListener('click', function () {
                    item.kuantitas++;
                    renderCart();
                    hitungTotal();
                });

                qtyCtrl.appendChild(minusB);
                qtyCtrl.appendChild(qtyIn);
                qtyCtrl.appendChild(plusB);

                var subEl = document.createElement('div');
                subEl.className = 'fw-bold tabular-nums';
                subEl.textContent = formatRupiah(lineSubtotal);

                bottomLine.appendChild(qtyCtrl);
                bottomLine.appendChild(subEl);

                row.appendChild(topLine);
                row.appendChild(bottomLine);
                cartListEl.appendChild(row);
            });
        }

        if (cartCountBadge) {
            cartCountBadge.textContent = totalQty + ' item';
        }
    }

    function hitungTotal() {
        var hasKasir = selectKasir && selectKasir.value.trim() !== '';
        var subtotal = 0;

        cartItems.forEach(function (it) {
            subtotal += Math.round(it.harga * it.kuantitas);
        });

        var pajak = Math.round(subtotal * 0.11);
        var total = subtotal + pajak;

        elSubtotal.textContent = formatRupiah(subtotal);
        elPajak.textContent    = formatRupiah(pajak);
        elTotal.textContent    = formatRupiah(total);

        btnSubmit.disabled = !(cartItems.length >= 1 && hasKasir);
    }

    function pilihMenu(card) {
        var id       = card.getAttribute('data-id') || '';
        var nama     = card.getAttribute('data-nama') || '';
        var harga    = parseFloat(card.getAttribute('data-harga')) || 0;
        var kategori = card.getAttribute('data-kategori') || 'Minuman';
        var inisial  = card.getAttribute('data-inisial') || 'MN';
        var gambar   = card.getAttribute('data-gambar') || '';

        currentProduct = {
            id: id,
            nama: nama,
            harga: harga,
            kategori: kategori,
            inisial: inisial,
            gambar: gambar
        };

        inputJumlah.value = 1;

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

        billsItem.classList.remove('d-none');

        toggleDrinkCustomization(kategori === 'Minuman', nama);
        syncMenuCardChecks();
    }

    if (btnCloseConfig) {
        btnCloseConfig.addEventListener('click', function () {
            currentProduct = null;
            billsItem.classList.add('d-none');
            syncMenuCardChecks();
        });
    }

    if (btnAddCart) {
        btnAddCart.addEventListener('click', function () {
            if (!currentProduct) {
                return;
            }
            var qtyToAdd = parseInt(inputJumlah.value, 10);
            if (isNaN(qtyToAdd) || qtyToAdd < 1) {
                qtyToAdd = 1;
            }
            var catatan = getActiveCatatan();
            var pid = String(currentProduct.id);

            var existing = null;
            for (var i = 0; i < cartItems.length; i++) {
                if (String(cartItems[i].id_produk) === pid && cartItems[i].catatan === catatan) {
                    existing = cartItems[i];
                    break;
                }
            }

            if (existing) {
                existing.kuantitas += qtyToAdd;
            } else {
                cartItems.push({
                    id_produk: currentProduct.id,
                    nama: currentProduct.nama,
                    harga: currentProduct.harga,
                    kategori: currentProduct.kategori,
                    kuantitas: qtyToAdd,
                    catatan: catatan
                });
            }

            inputJumlah.value = 1;
            renderCart();
            syncMenuCardChecks();
            hitungTotal();
        });
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
            }
        });
    }

    if (btnPlus) {
        btnPlus.addEventListener('click', function () {
            var current = parseInt(inputJumlah.value, 10) || 0;
            inputJumlah.value = current + 1;
        });
    }

    if (inputJumlah) {
        inputJumlah.addEventListener('change', function () {
            var val = parseInt(inputJumlah.value, 10);
            if (isNaN(val) || val < 1) {
                inputJumlah.value = 1;
            }
        });
    }

    function applyMenuFilter() {
        var keyword = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var visibleCount = 0;

        menuCols.forEach(function (col) {
            var namaMenu = col.getAttribute('data-nama-search') || '';
            var katMenu  = col.getAttribute('data-kategori') || 'Minuman';
            var matchKat = (activeFilterKat === 'Semua' || katMenu === activeFilterKat);
            var matchKey = (keyword === '' || namaMenu.indexOf(keyword) !== -1);

            if (matchKat && matchKey) {
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
    }

    filterMenuChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            activeFilterKat = chip.getAttribute('data-filter-menu') || 'Semua';
            filterMenuChips.forEach(function (c) {
                var isMatch = (c === chip);
                c.classList.toggle('is-active', isMatch);
                c.setAttribute('aria-pressed', isMatch ? 'true' : 'false');
            });
            applyMenuFilter();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyMenuFilter);
    }

    renderCart();
    hitungTotal();

    try {
        var pid = new URLSearchParams(window.location.search).get('id_produk');
        if (pid) {
            var targetSelector = window.CSS && typeof window.CSS.escape === 'function'
                ? '[data-id="' + window.CSS.escape(pid) + '"]'
                : '[data-id="' + pid.replace(/(["\\])/g, '\\$1') + '"]';
            var card = document.querySelector(targetSelector);
            if (card) {
                card.click();
                if (typeof card.scrollIntoView === 'function') {
                    card.scrollIntoView({ block: 'center', behavior: 'smooth' });
                }
            }
        }
    } catch (e) {}
})();
</script>

<?php
include ROOTPATH . '/include/footer.php';
?>
