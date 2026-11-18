<?php
if (!defined('ROOTPATH')) {
    define('ROOTPATH', dirname(__DIR__));
}
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

// Tanggal hari ini dalam format Indonesia (WITA)
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
$today_ymd  = date('Y-m-d');
$today_name = $hari_indo[date('l')] ?? date('l');
$today_label = $today_name . ', ' . formatTanggalIndo($today_ymd) . ' WITA';

// 1. Statistik Utama
$total_kasir = 0;
$res_kasir = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kasir");
if ($res_kasir && ($row_kasir = mysqli_fetch_assoc($res_kasir))) {
    $total_kasir = (int)$row_kasir['total'];
}

$total_produk = 0;
$res_produk = mysqli_query($koneksi, "SHOW TABLES LIKE 'produk'");
if ($res_produk && mysqli_num_rows($res_produk) > 0) {
    $rp = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk");
    if ($rp && ($row_produk = mysqli_fetch_assoc($rp))) {
        $total_produk = (int)$row_produk['total'];
    }
}

$total_transaksi = 0;
$total_pendapatan = 0.0;
$res_trans = mysqli_query($koneksi, "SHOW TABLES LIKE 'transaksi'");
$has_transaksi_table = ($res_trans && mysqli_num_rows($res_trans) > 0);
if ($has_transaksi_table) {
    $rt = mysqli_query($koneksi, "SELECT COUNT(*) AS total_trx, COALESCE(SUM(total), 0) AS total_rev FROM transaksi");
    if ($rt && ($row_trans = mysqli_fetch_assoc($rt))) {
        $total_transaksi  = (int)$row_trans['total_trx'];
        $total_pendapatan = (float)$row_trans['total_rev'];
    }
}

// 2. Pendapatan 7 Hari Terakhir (WITA)
$pendapatan_7_hari = [];
for ($i = 6; $i >= 0; $i--) {
    $ts  = strtotime("-$i days");
    $ymd = date('Y-m-d', $ts);
    $parts = explode(' ', formatTanggalIndo($ymd));
    $tgl_pendek = isset($parts[0], $parts[1]) ? ($parts[0] . ' ' . $parts[1]) : $ymd;

    $pendapatan_7_hari[$ymd] = [
        'tanggal'    => $ymd,
        'hari'       => $hari_singkat[date('l', $ts)] ?? date('D', $ts),
        'tgl_pendek' => $tgl_pendek,
        'total'      => 0.0,
        'is_today'   => ($ymd === $today_ymd),
    ];
}

$max_harian = 0.0;
if ($has_transaksi_table) {
    $start_7d = date('Y-m-d', strtotime('-6 days'));
    $stmt_7d  = mysqli_prepare(
        $koneksi,
        "SELECT tanggal, COALESCE(SUM(total), 0) AS total_hari
         FROM transaksi
         WHERE tanggal BETWEEN ? AND ?
         GROUP BY tanggal"
    );
    if ($stmt_7d) {
        mysqli_stmt_bind_param($stmt_7d, 'ss', $start_7d, $today_ymd);
        mysqli_stmt_execute($stmt_7d);
        $res_7d = mysqli_stmt_get_result($stmt_7d);
        if ($res_7d) {
            while ($r7 = mysqli_fetch_assoc($res_7d)) {
                $tgl_key = $r7['tanggal'];
                $nilai   = (float)$r7['total_hari'];
                if (isset($pendapatan_7_hari[$tgl_key])) {
                    $pendapatan_7_hari[$tgl_key]['total'] = $nilai;
                    if ($nilai > $max_harian) {
                        $max_harian = $nilai;
                    }
                }
            }
        }
        mysqli_stmt_close($stmt_7d);
    }
}

/**
 * Format nominal ringkas di atas batang grafik (contoh: Rp 125rb atau Rp 0).
 */
function formatRupiahRingkas($angka)
{
    $angka = (float)$angka;
    if ($angka <= 0) {
        return 'Rp 0';
    }
    if ($angka >= 1000000) {
        $jt = round($angka / 1000000, 1);
        return 'Rp ' . str_replace('.', ',', (string)$jt) . 'jt';
    }
    if ($angka >= 1000) {
        return 'Rp ' . number_format(round($angka / 1000), 0, ',', '.') . 'rb';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// 3. Menu Terlaris (Top 5 berdasarkan SUM(kuantitas))
$menu_terlaris = [];
$max_terjual = 0;
if ($has_transaksi_table) {
    $query_top = "SELECT p.id_produk, p.nama_produk, p.gambar, COALESCE(SUM(dt.kuantitas), 0) AS total_terjual
                  FROM detail_transaksi dt
                  INNER JOIN produk p ON dt.id_produk = p.id_produk
                  GROUP BY p.id_produk, p.nama_produk, p.gambar
                  HAVING total_terjual > 0
                  ORDER BY total_terjual DESC, p.nama_produk ASC
                  LIMIT 5";
    $res_top = mysqli_query($koneksi, $query_top);
    if ($res_top) {
        while ($row_top = mysqli_fetch_assoc($res_top)) {
            $terjual = (int)$row_top['total_terjual'];
            if ($terjual > $max_terjual) {
                $max_terjual = $terjual;
            }
            $menu_terlaris[] = $row_top;
        }
    }
}

// 4. Transaksi Terbaru (5 Transaksi Terakhir beserta rincian menu & kasir)
$transaksi_terbaru = [];
$trx_ids = [];
if ($has_transaksi_table) {
    $res_trans_list = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id_transaksi DESC LIMIT 5");
    if ($res_trans_list) {
        while ($row = mysqli_fetch_assoc($res_trans_list)) {
            $id_t = (int)$row['id_transaksi'];
            $row['nama_kasir'] = '-';
            $row['items'] = [];
            $transaksi_terbaru[$id_t] = $row;
            $trx_ids[] = $id_t;
        }
    }
}

if (!empty($trx_ids)) {
    $ids_sql = implode(',', array_map('intval', $trx_ids));
    $res_det = mysqli_query(
        $koneksi,
        "SELECT dt.id_transaksi, dt.kuantitas, dt.catatan, p.nama_produk, k.nama_kasir
         FROM detail_transaksi dt
         LEFT JOIN produk p ON dt.id_produk = p.id_produk
         LEFT JOIN kasir k ON dt.id_kasir = k.id_kasir
         WHERE dt.id_transaksi IN ($ids_sql)
         ORDER BY dt.id_detail_transaksi ASC"
    );
    if ($res_det) {
        while ($det = mysqli_fetch_assoc($res_det)) {
            $id_t = (int)$det['id_transaksi'];
            if (!isset($transaksi_terbaru[$id_t])) {
                continue;
            }
            if ($transaksi_terbaru[$id_t]['nama_kasir'] === '-' && !empty($det['nama_kasir'])) {
                $transaksi_terbaru[$id_t]['nama_kasir'] = $det['nama_kasir'];
            }
            if (!empty($det['nama_produk'])) {
                $transaksi_terbaru[$id_t]['items'][] = [
                    'nama_produk' => $det['nama_produk'],
                    'kuantitas'   => (int)($det['kuantitas'] ?? 0),
                    'catatan'     => trim((string)($det['catatan'] ?? '')),
                ];
            }
        }
    }
}
?>

<div class="container-fluid px-0">

    <!-- 1. Header Dashboard -->
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

    <!-- 2. 4 Kartu Statistik Sejajar (row g-4, col-sm-6 col-xl-3) -->
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

    <!-- 3. Baris Tengah: Grafik Pendapatan 7 Hari Terakhir & Menu Terlaris -->
    <div class="row g-4 mt-1 mb-4">

        <!-- Kolom Kiri (col-lg-7): Pendapatan 7 Hari Terakhir (WITA) -->
        <div class="col-lg-7">
            <div class="card-soft h-100 d-flex flex-column justify-content-between">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
                    <div>
                        <h2 class="h5 fw-semibold mb-1 card-title">Pendapatan 7 Hari Terakhir</h2>
                        <p class="page-subtitle mb-0">Akumulasi penjualan harian zona waktu WITA</p>
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
                        <div class="chart-7d-amount"><?php echo htmlspecialchars(formatRupiahRingkas($nilai_hari), ENT_QUOTES, 'UTF-8'); ?></div>
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

        <!-- Kolom Kanan (col-lg-5): Menu Terlaris (Top 5) -->
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

    <!-- 4. Baris Bawah: Transaksi Terbaru (5 Transaksi Terakhir) -->
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
                                <?php $is_multi = count($trx['items']) > 1; ?>
                                <?php foreach ($trx['items'] as $idx_item => $item): ?>
                                    <div class="<?php echo $idx_item > 0 ? 'mt-1' : ''; ?>">
                                        <span class="fw-medium"><?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($is_multi): ?>
                                            <span class="text-muted tabular-nums">(x<?php echo (int)$item['kuantitas']; ?>)</span>
                                        <?php endif; ?>
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

    // 1. Trigger animasi tumbuh batang grafik 7 hari & bar menu terlaris
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

    // 2. Animasi Count-Up 650ms pada 4 kartu statistik
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
