<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$filter           = Transaksi::buatFilter($_GET);
$query_string     = $_SERVER['QUERY_STRING'] ?? '';
$print_report_url = 'print_report.php' . ($query_string !== '' ? '?' . htmlspecialchars($query_string, ENT_QUOTES, 'UTF-8') : '');
$riwayat          = Transaksi::ambilRiwayat($filter);
$daftar_transaksi = $riwayat['rows'];
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Riwayat Transaksi</h1>
            <p class="page-subtitle">
                Periode <strong style="color: var(--text-main);"><?php echo htmlspecialchars($filter['periode_label'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo $print_report_url; ?>" target="_blank" class="btn-coffee-outline">Cetak Laporan</a>
            <a href="/starbuck/pages/transactions/add.php" class="btn-coffee">+ Transaksi Baru</a>
        </div>
    </div>

    <div class="card-soft mb-4">
        <form action="list.php" method="GET" id="form-filter-transaksi">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-3">
                    <label for="tanggal_awal" class="form-label fw-medium">Dari Tanggal</label>
                    <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control tabular-nums"
                        value="<?php echo htmlspecialchars($filter['tanggal_awal'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="tanggal_akhir" class="form-label fw-medium">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control tabular-nums"
                        value="<?php echo htmlspecialchars($filter['tanggal_akhir'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="bulan" class="form-label fw-medium">Pilih Bulan</label>
                    <input type="month" name="bulan" id="bulan" class="form-control tabular-nums"
                        value="<?php echo htmlspecialchars($filter['bulan'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3 d-grid">
                    <button type="submit" class="btn-coffee btn-block">Terapkan</button>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 mt-3 border-top" style="border-color: var(--border-soft) !important;">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="page-subtitle me-1 mb-0">Filter </span>
                    <a href="list.php?mode=hari_ini"
                       class="<?php echo $filter['active_mode'] === 'hari_ini' ? 'btn-coffee' : 'btn-neutral-outline'; ?> btn-sm-soft">
                        Hari Ini
                    </a>
                    <a href="list.php"
                       class="<?php echo $filter['active_mode'] === 'bulan_ini' ? 'btn-coffee' : 'btn-neutral-outline'; ?> btn-sm-soft">
                        Bulan Ini
                    </a>
                    <a href="list.php?mode=semua"
                       class="<?php echo $filter['active_mode'] === 'semua' ? 'btn-coffee' : 'btn-neutral-outline'; ?> btn-sm-soft">
                        Semua
                    </a>
                </div>
                <div class="search-box" style="width: 280px; max-width: 100%;">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="search-transaksi" class="form-control"
                        placeholder="Cari nama menu atau kasir..." autocomplete="off" aria-label="Cari nama menu atau kasir">
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($daftar_transaksi)): ?>
        <div class="card-soft empty-state">
            Tidak ada transaksi pada periode ini.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3" id="transaksi-grid">
            <?php $no = 1; foreach ($daftar_transaksi as $row):
                $nomor_urut    = $no++;
                $id_trx_str    = (string)$row['id_transaksi'];
                $no_nota       = !empty($row['id_nota']) ? $row['id_nota'] : ('#' . str_pad($id_trx_str, 4, '0', STR_PAD_LEFT));
                $tgl_indo      = formatTanggalIndo($row['tanggal']);
                $tgl_dmy       = date('d/m/Y', strtotime($row['tanggal']));
                $all_items     = is_array($row['items']) ? $row['items'] : [];
                $preview_items = array_slice($all_items, 0, 3);
                $sisa_item     = max(0, count($all_items) - count($preview_items));
                $total_qty     = $row['total_kuantitas'] !== null ? (int)$row['total_kuantitas'] : 0;

                $search_parts = [$row['nama_kasir'], $no_nota, $tgl_indo, '#' . $nomor_urut];
                foreach ($all_items as $it_s) {
                    $search_parts[] = $it_s['nama_produk'] ?? '';
                    $search_parts[] = $it_s['catatan'] ?? '';
                }
                $trx_search_key = strtolower(trim(implode(' ', $search_parts)));

                $receipt_payload = [
                    'id_transaksi' => (int)$row['id_transaksi'],
                    'no_nota'      => $no_nota,
                    'nama_kasir'   => $row['nama_kasir'],
                    'tanggal'      => $tgl_dmy,
                    'waktu'        => $row['waktu'] . ' WITA',
                    'subtotal'     => (float)$row['subtotal'],
                    'pajak'        => (float)$row['pajak'],
                    'total'        => (float)$row['total'],
                    'items'        => $all_items,
                    'print_url'    => 'print_receipt.php?id_transaksi=' . urlencode($id_trx_str) . '&autoprint=1',
                ];
            ?>
            <div class="col transaksi-item-col" data-trx-search="<?php echo htmlspecialchars($trx_search_key, ENT_QUOTES, 'UTF-8'); ?>">
                <article class="card trx-order-card h-100 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <span class="trx-order-badge tabular-nums">#<?php echo $nomor_urut; ?></span>
                            <div class="min-w-0">
                                <div class="trx-order-cashier text-truncate"><?php echo htmlspecialchars($row['nama_kasir'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="trx-order-nota tabular-nums"><?php echo htmlspecialchars($no_nota, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        <span class="badge-category tabular-nums flex-shrink-0"><?php echo $total_qty; ?> Item</span>
                    </div>

                    <div class="trx-order-meta tabular-nums">
                        <span><?php echo htmlspecialchars($tgl_indo, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?php echo htmlspecialchars($row['waktu'], ENT_QUOTES, 'UTF-8'); ?> WITA</span>
                    </div>

                    <div class="trx-items-wrap">
                        <div class="trx-items-head">
                            <span>Menu</span>
                            <span class="text-center">Qty</span>
                            <span class="text-end">Harga</span>
                        </div>

                        <?php if (empty($preview_items)): ?>
                            <div class="trx-items-empty">Rincian item tidak tersedia</div>
                        <?php else: ?>
                            <div class="trx-items-list">
                                <?php foreach ($preview_items as $item):
                                    $qty_it   = (int)($item['kuantitas'] ?? 0);
                                    $line_rp  = round((float)($item['harga_satuan'] ?? 0) * $qty_it);
                                ?>
                                <div class="trx-item-row">
                                    <div class="min-w-0">
                                        <div class="trx-item-name text-truncate"><?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php if ($item['catatan'] !== ''): ?>
                                            <div class="trx-item-note text-truncate"><?php echo htmlspecialchars($item['catatan'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="trx-item-qty tabular-nums"><?php echo $qty_it; ?></div>
                                    <div class="trx-item-price tabular-nums">Rp <?php echo number_format($line_rp, 0, ',', '.'); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($sisa_item > 0): ?>
                                <div class="trx-more-items tabular-nums">+<?php echo $sisa_item; ?> menu lainnya</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="trx-order-footer mt-auto">
                        <div class="d-flex align-items-baseline justify-content-between gap-2 mb-3">
                            <span class="trx-total-label">Total</span>
                            <span class="trx-total-value tabular-nums">Rp <?php echo number_format((float)$row['total'], 0, ',', '.'); ?></span>
                        </div>
                        <a href="print_receipt.php?id_transaksi=<?php echo urlencode($id_trx_str); ?>"
                           target="_blank"
                           class="btn-coffee-outline btn-sm-soft btn-block btn-preview-receipt"
                           data-receipt="<?php echo htmlspecialchars(json_encode($receipt_payload, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                            Pratinjau &amp; Cetak Struk
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card-soft empty-state d-none mt-3" id="transaksi-search-empty">
            Tidak ada transaksi yang cocok dengan pencarian nama menu atau kasir.
        </div>
    <?php endif; ?>

</div>

<div class="modal fade sb-modal receipt-preview-modal" id="sbReceiptModal" tabindex="-1" aria-labelledby="sbReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="receipt-paper">
                    <div class="receipt-paper-header text-center mb-3">
                        <img src="../../assets/img/logo-starbuck.png" alt="Starbuck" class="receipt-paper-logo mb-2"
                             onerror="this.style.display='none';">
                        <h2 class="receipt-paper-brand mb-0" id="sbReceiptModalLabel">Starbuck POS</h2>
                        <div class="receipt-paper-sub">Struk Pembayaran Resmi</div>
                    </div>

                    <hr class="receipt-paper-divider">

                    <div class="receipt-paper-row">
                        <span class="receipt-paper-label">No. Nota</span>
                        <span class="receipt-paper-val tabular-nums" id="rcp-nota">#0000</span>
                    </div>
                    <div class="receipt-paper-row">
                        <span class="receipt-paper-label">Kasir</span>
                        <span class="receipt-paper-val" id="rcp-kasir">-</span>
                    </div>
                    <div class="receipt-paper-row">
                        <span class="receipt-paper-label">Tanggal</span>
                        <span class="receipt-paper-val tabular-nums" id="rcp-tanggal">-</span>
                    </div>
                    <div class="receipt-paper-row">
                        <span class="receipt-paper-label">Waktu</span>
                        <span class="receipt-paper-val tabular-nums" id="rcp-waktu">-</span>
                    </div>

                    <hr class="receipt-paper-divider">

                    <div id="rcp-items"></div>

                    <hr class="receipt-paper-divider">

                    <div class="receipt-paper-row">
                        <span class="receipt-paper-label">Subtotal</span>
                        <span class="receipt-paper-val tabular-nums" id="rcp-subtotal">Rp 0</span>
                    </div>
                    <div class="receipt-paper-row">
                        <span class="receipt-paper-label">Pajak (11%)</span>
                        <span class="receipt-paper-val tabular-nums" id="rcp-pajak">Rp 0</span>
                    </div>

                    <hr class="receipt-paper-divider">

                    <div class="receipt-paper-row receipt-paper-grand">
                        <span class="receipt-paper-label">Total</span>
                        <span class="receipt-paper-val tabular-nums" id="rcp-total">Rp 0</span>
                    </div>

                    <hr class="receipt-paper-divider">

                    <div class="receipt-paper-footer text-center">
                        <div>Terima kasih atas kunjungan Anda.</div>
                        <div>Simpan struk ini sebagai bukti pembayaran.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between px-4 py-3">
                <button type="button" class="btn-neutral-outline btn-sm-soft" data-bs-dismiss="modal">Tutup</button>
                <a href="#" target="_blank" id="rcp-btn-print" class="btn-coffee btn-sm-soft">Cetak Sekarang</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var inputAwal  = document.getElementById('tanggal_awal');
    var inputAkhir = document.getElementById('tanggal_akhir');
    var inputBulan = document.getElementById('bulan');

    function clearBulan() {
        if (inputBulan && (inputAwal.value !== '' || inputAkhir.value !== '')) {
            inputBulan.value = '';
        }
    }

    if (inputAwal) {
        inputAwal.addEventListener('change', clearBulan);
    }
    if (inputAkhir) {
        inputAkhir.addEventListener('change', clearBulan);
    }
    if (inputBulan) {
        inputBulan.addEventListener('change', function () {
            if (inputBulan.value !== '') {
                if (inputAwal) inputAwal.value = '';
                if (inputAkhir) inputAkhir.value = '';
            }
        });
    }

    var searchInput = document.getElementById('search-transaksi');
    var trxCols     = document.querySelectorAll('.transaksi-item-col');
    var emptyState  = document.getElementById('transaksi-search-empty');

    if (searchInput && trxCols.length > 0) {
        searchInput.addEventListener('input', function () {
            var keyword = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;

            trxCols.forEach(function (col) {
                var key = col.getAttribute('data-trx-search') || '';
                if (keyword === '' || key.indexOf(keyword) !== -1) {
                    col.classList.remove('d-none');
                    visibleCount++;
                } else {
                    col.classList.add('d-none');
                }
            });

            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('d-none');
                } else {
                    emptyState.classList.add('d-none');
                }
            }
        });
    }

    function formatRp(num) {
        return 'Rp ' + Math.round(Number(num) || 0).toLocaleString('id-ID');
    }

    var receiptModalEl = document.getElementById('sbReceiptModal');
    var elNota         = document.getElementById('rcp-nota');
    var elKasir        = document.getElementById('rcp-kasir');
    var elTanggal      = document.getElementById('rcp-tanggal');
    var elWaktu        = document.getElementById('rcp-waktu');
    var elItems        = document.getElementById('rcp-items');
    var elSubtotal     = document.getElementById('rcp-subtotal');
    var elPajak        = document.getElementById('rcp-pajak');
    var elTotal        = document.getElementById('rcp-total');
    var btnPrintNow    = document.getElementById('rcp-btn-print');
    var bsReceiptModal = null;

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-preview-receipt');
        if (!btn) {
            return;
        }
        var raw = btn.getAttribute('data-receipt');
        if (!raw || !receiptModalEl || !window.bootstrap || typeof window.bootstrap.Modal !== 'function') {
            return;
        }

        var data = null;
        try {
            data = JSON.parse(raw);
        } catch (err) {
            return;
        }
        if (!data) {
            return;
        }

        e.preventDefault();

        if (!bsReceiptModal) {
            bsReceiptModal = new window.bootstrap.Modal(receiptModalEl);
        }

        if (elNota) elNota.textContent = data.no_nota || '#0000';
        if (elKasir) elKasir.textContent = data.nama_kasir || '-';
        if (elTanggal) elTanggal.textContent = data.tanggal || '-';
        if (elWaktu) elWaktu.textContent = data.waktu || '-';
        if (elSubtotal) elSubtotal.textContent = formatRp(data.subtotal);
        if (elPajak) elPajak.textContent = formatRp(data.pajak);
        if (elTotal) elTotal.textContent = formatRp(data.total);
        if (btnPrintNow) btnPrintNow.href = data.print_url || btn.href;

        if (elItems) {
            elItems.innerHTML = '';
            var items = Array.isArray(data.items) ? data.items : [];
            if (items.length === 0) {
                var emptyItem = document.createElement('div');
                emptyItem.className = 'receipt-paper-item-name';
                emptyItem.textContent = 'Detail item tidak tersedia';
                elItems.appendChild(emptyItem);
            } else {
                items.forEach(function (it) {
                    var block = document.createElement('div');
                    block.className = 'receipt-paper-item';

                    var nameEl = document.createElement('div');
                    nameEl.className = 'receipt-paper-item-name';
                    nameEl.textContent = it.nama_produk || '-';
                    block.appendChild(nameEl);

                    if (it.catatan && String(it.catatan).trim() !== '') {
                        var noteEl = document.createElement('div');
                        noteEl.className = 'receipt-paper-item-note';
                        noteEl.textContent = 'Catatan: ' + String(it.catatan).trim();
                        block.appendChild(noteEl);
                    }

                    var qty = parseInt(it.kuantitas, 10) || 0;
                    var hargaSatuan = parseFloat(it.harga_satuan) || 0;
                    var lineTotal = hargaSatuan * qty;

                    var detailEl = document.createElement('div');
                    detailEl.className = 'receipt-paper-item-detail tabular-nums';

                    var leftSpan = document.createElement('span');
                    leftSpan.textContent = qty + ' \u00d7 ' + formatRp(hargaSatuan);

                    var rightSpan = document.createElement('span');
                    rightSpan.className = 'fw-semibold';
                    rightSpan.style.color = 'var(--text-main)';
                    rightSpan.textContent = formatRp(lineTotal);

                    detailEl.appendChild(leftSpan);
                    detailEl.appendChild(rightSpan);
                    block.appendChild(detailEl);

                    elItems.appendChild(block);
                });
            }
        }

        bsReceiptModal.show();
    });
})();
</script>

<?php
include ROOTPATH . '/include/footer.php';
?>
