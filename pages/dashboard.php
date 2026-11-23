<?php
if (!defined('ROOTPATH')) {
    define('ROOTPATH', dirname(__DIR__));
}
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$hari_indo = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
];
$hari_singkat = [
    'Sunday'    => 'Min',
    'Monday'    => 'Sen',
    'Tuesday'   => 'Sel',
    'Wednesday' => 'Rab',
    'Thursday'  => 'Kam',
    'Friday'    => 'Jum',
    'Saturday'  => 'Sab',
];
$today_ymd   = date('Y-m-d');
$today_name  = $hari_indo[date('l')] ?? date('l');
$today_label = $today_name . ', ' . Format::tanggalIndo($today_ymd) . ' WITA';

$statistik        = Transaksi::statistik();
$total_kasir      = $statistik['total_kasir'];
$total_produk     = $statistik['total_produk'];
$total_transaksi  = $statistik['total_transaksi'];
$total_pendapatan = $statistik['total_pendapatan'];

$grafik_7d         = Transaksi::pendapatan7Hari($hari_singkat);
$pendapatan_7_hari = $grafik_7d['days'];
$max_harian        = $grafik_7d['max_harian'];

$top_menu      = Transaksi::menuTerlaris(5);
$menu_terlaris = $top_menu['items'];
$max_terjual   = $top_menu['max_terjual'];

$transaksi_terbaru = Transaksi::terbaru(5);
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">
                Ringkasan Operasional Starbuck, <span class="tabular-nums"><?php echo htmlspecialchars($today_label, ENT_QUOTES, 'UTF-8'); ?></span>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo $sb_base_url; ?>/pages/transactions/print_report.php" target="_blank" class="btn-coffee-outline">Cetak Laporan</a>
            <a href="<?php echo $sb_base_url; ?>/pages/transactions/add.php" class="btn-coffee">+ Transaksi Baru</a>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Kasir</div>
                    <div class="metric-value fw-bold mb-3" data-countup="<?php echo $total_kasir; ?>">
                        <?php echo number_format($total_kasir, 0, ',', '.'); ?>
                    </div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/cashiers/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Produk</div>
                    <div class="metric-value fw-bold mb-3" data-countup="<?php echo $total_produk; ?>">
                        <?php echo number_format($total_produk, 0, ',', '.'); ?>
                    </div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/products/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Transaksi</div>
                    <div class="metric-value fw-bold mb-3" data-countup="<?php echo $total_transaksi; ?>">
                        <?php echo number_format($total_transaksi, 0, ',', '.'); ?>
                    </div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/transactions/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card-soft card-forest h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="label-caps mb-2">Total Pendapatan</div>
                    <div class="metric-value fw-bold mb-3"
                         data-countup="<?php echo round($total_pendapatan); ?>"
                         data-countup-prefix="Rp ">
                        Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?>
                    </div>
                </div>
                <div>
                    <a href="<?php echo $sb_base_url; ?>/pages/transactions/list.php" class="link-arrow">Lihat Data &rarr;</a>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mt-1 mb-4">

        <div class="col-lg-7">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
                    <div>
                        <h2 class="h5 fw-semibold mb-1 card-title">Pendapatan 7 Hari Terakhir</h2>
                    </div>
                    <span class="badge-category">7 Hari</span>
                </div>

                <div class="chart-7d-grid" id="chart-7d-grid" role="region" aria-label="Grafik pendapatan 7 hari terakhir">
                    <?php foreach ($pendapatan_7_hari as $item_hari):
                        $nilai_hari = $item_hari['total'];
                        $is_zero    = ($nilai_hari <= 0);
                        if ($max_harian > 0 && !$is_zero) {
                            $height_pct = max(10, min(100, round(($nilai_hari / $max_harian) * 100)));
                        } else {
                            $height_pct = 4;
                        }
                        $full_rupiah = 'Rp ' . number_format($nilai_hari, 0, ',', '.');
                    ?>
                    <div class="chart-7d-col<?php echo $item_hari['is_today'] ? ' is-today' : ''; ?>"
                         title="<?php echo htmlspecialchars($item_hari['tgl_pendek'] . ' - ' . $full_rupiah, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="chart-7d-amount"><?php echo htmlspecialchars(Format::rupiahRingkas($nilai_hari), ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="chart-7d-track">
                            <div class="chart-7d-bar<?php echo $is_zero ? ' is-zero' : ''; ?>"
                                 style="height: <?php echo (int)$height_pct; ?>%;"></div>
                        </div>
                        <div class="chart-7d-day"><?php echo htmlspecialchars($item_hari['hari'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="chart-7d-date"><?php echo htmlspecialchars($item_hari['tgl_pendek'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-baseline gap-2 mb-3">
                        <div>
                            <h2 class="h5 fw-semibold mb-1 card-title">Menu Terlaris</h2>
                            <p class="page-subtitle mb-0">5 produk dengan penjualan tertinggi</p>
                        </div>
                        <a href="<?php echo $sb_base_url; ?>/pages/products/list.php" class="link-arrow">Katalog &rarr;</a>
                    </div>

                    <?php if (empty($menu_terlaris)): ?>
                        <div class="empty-state py-4">
                            Belum ada data item terjual pada riwayat transaksi.
                        </div>
                    <?php else: ?>
                        <div class="top-menu-list" id="top-menu-list">
                            <?php foreach ($menu_terlaris as $top):
                                $nama_top  = trim($top['nama_produk'] ?? '');
                                $inisial   = strtoupper(substr($nama_top !== '' ? $nama_top : 'MN', 0, 2));
                                $gambar    = trim($top['gambar'] ?? '');
                                $has_img   = $gambar !== '' && file_exists(ROOTPATH . '/assets/img/products/' . basename($gambar));
                                $terjual   = (int)$top['total_terjual'];
                                $lebar_pct = $max_terjual > 0 ? max(8, min(100, round(($terjual / $max_terjual) * 100))) : 8;
                            ?>
                            <div class="top-menu-item">
                                <?php if ($has_img): ?>
                                    <img src="<?php echo $sb_base_url; ?>/assets/img/products/<?php echo rawurlencode(basename($gambar)); ?>"
                                         alt="<?php echo htmlspecialchars($nama_top, ENT_QUOTES, 'UTF-8'); ?>"
                                         class="top-menu-thumb"
                                         onerror="this.classList.add('d-none'); if(this.nextElementSibling){this.nextElementSibling.classList.remove('d-none');}">
                                    <span class="top-menu-initial d-none" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php else: ?>
                                    <span class="top-menu-initial" aria-hidden="true"><?php echo htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>

                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                                        <span class="fw-semibold text-truncate product-card-title">
                                            <?php echo htmlspecialchars($nama_top, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <span class="page-subtitle tabular-nums flex-shrink-0 mt-0">
                                            <?php echo number_format($terjual, 0, ',', '.'); ?> terjual
                                        </span>
                                    </div>
                                    <div class="top-menu-bar-track">
                                        <div class="top-menu-bar-fill" style="width: <?php echo (int)$lebar_pct; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="card-soft card-soft-flush">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
            <div>
                <h2 class="h5 fw-semibold mb-1 card-title">Transaksi Terbaru</h2>
                <p class="page-subtitle mb-0">5 transaksi terakhir yang tercatat</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo $sb_base_url; ?>/pages/transactions/list.php" class="btn-coffee-outline">Lihat Semua</a>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table-soft">
                <thead>
                    <tr>
                        <th scope="col" class="col-index">No</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Waktu (WITA)</th>
                        <th scope="col">Kasir</th>
                        <th scope="col">Menu</th>
                        <th scope="col" class="col-money">Subtotal</th>
                        <th scope="col" class="col-money">Pajak (11%)</th>
                        <th scope="col" class="col-money">Total</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi_terbaru)): ?>
                    <tr>
                        <td colspan="9" class="empty-state">Belum ada transaksi yang tercatat. Klik "+ Transaksi Baru" untuk memulai.</td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($transaksi_terbaru as $trx): ?>
                    <tr>
                        <td class="col-index"><?php echo $no++; ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars(formatTanggalIndo($trx['tanggal']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars($trx['waktu'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($trx['nama_kasir'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (empty($trx['items'])): ?>
                                -
                            <?php else: ?>
                                <?php foreach ($trx['items'] as $idx_item => $item): ?>
                                    <div class="<?php echo $idx_item > 0 ? 'mt-1' : ''; ?>">
                                        <span class="fw-medium"><?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="text-muted tabular-nums">(<?php echo (int)$item['kuantitas']; ?>x)</span>
                                        <?php if ($item['catatan'] !== ''): ?>
                                            <span class="badge-category ms-1"><?php echo htmlspecialchars($item['catatan'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td class="col-money">Rp <?php echo number_format((float)$trx['subtotal'], 0, ',', '.'); ?></td>
                        <td class="col-money">Rp <?php echo number_format((float)$trx['pajak'], 0, ',', '.'); ?></td>
                        <td class="col-money fw-bold">Rp <?php echo number_format((float)$trx['total'], 0, ',', '.'); ?></td>
                        <td class="text-end">
                            <a href="<?php echo $sb_base_url; ?>/pages/transactions/print_receipt.php?id_transaksi=<?php echo urlencode((string)$trx['id_transaksi']); ?>"
                               target="_blank" class="btn-coffee-outline btn-sm-soft">Cetak Struk</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var chartGrid = document.getElementById('chart-7d-grid');
    var topList   = document.getElementById('top-menu-list');

    if (reducedMotion) {
        if (chartGrid) chartGrid.classList.add('is-ready');
        if (topList) topList.classList.add('is-ready');
    } else {
        requestAnimationFrame(function () {
            if (chartGrid) chartGrid.classList.add('is-ready');
            if (topList) topList.classList.add('is-ready');
        });
    }

    if (reducedMotion) {
        return;
    }

    var countEls = document.querySelectorAll('[data-countup]');
    var duration = 650;

    countEls.forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-countup')) || 0;
        var prefix = el.getAttribute('data-countup-prefix') || '';
        if (target <= 0) {
            el.textContent = prefix + '0';
            return;
        }

        var startTime = null;
        function step(timestamp) {
            if (!startTime) {
                startTime = timestamp;
            }
            var progress = Math.min(1, (timestamp - startTime) / duration);
            var eased    = 1 - Math.pow(1 - progress, 3);
            var current  = Math.round(target * eased);
            el.textContent = prefix + current.toLocaleString('id-ID');

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = prefix + Math.round(target).toLocaleString('id-ID');
            }
        }

        requestAnimationFrame(step);
    });
})();
</script>

<?php include ROOTPATH . '/include/footer.php'; ?>
