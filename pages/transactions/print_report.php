<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/config/config.php';

// Deteksi nama kolom primary key pada tabel produk (id_produk atau id)
$col_id_produk = 'id';
$check_prod_pk = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
if ($check_prod_pk && mysqli_num_rows($check_prod_pk) > 0) {
    $col_id_produk = 'id_produk';
}

// Deteksi nama kolom jumlah pada tabel detail_transaksi
$col_jumlah = 'jumlah';
$res_dt_cols = mysqli_query($koneksi, "SHOW COLUMNS FROM detail_transaksi");
if ($res_dt_cols) {
    while ($col = mysqli_fetch_assoc($res_dt_cols)) {
        if (in_array($col['Field'], ['jumlah', 'qty', 'kuantitas'], true)) {
            $col_jumlah = $col['Field'];
            break;
        }
    }
}

$query = "SELECT t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu,
                 dt.$col_jumlah AS jumlah_beli, p.nama_produk
          FROM transaksi t
          LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
          LEFT JOIN produk p ON dt.id_produk = p.$col_id_produk
          ORDER BY t.id_transaksi DESC";

$result = mysqli_query($koneksi, $query);
$rows = [];
$total_pendapatan = 0;
$seen_trx = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
        $tid = $row['id_transaksi'];
        if (!isset($seen_trx[$tid])) {
            $seen_trx[$tid] = true;
            $total_pendapatan += (float)$row['total'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Penjualan - Starbuck</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f6f8;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }
        .report-container {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            padding: 28px 32px;
            border-radius: 8px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }
        .no-print {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 20px;
        }
        .btn {
            font-size: 14px;
            font-weight: 600;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print {
            background-color: #00704a;
            color: #fff;
        }
        .btn-print:hover {
            background-color: #005a3b;
        }
        .btn-back {
            background-color: #6c757d;
            color: #fff;
        }
        .report-header {
            text-align: center;
            border-bottom: 2px solid #00704a;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 1px;
            color: #00704a;
        }
        .report-header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 9px 10px;
            text-align: left;
        }
        thead th {
            background-color: #00704a;
            color: #fff;
            font-weight: 600;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tfoot td {
            background-color: #ecfdf5;
            font-weight: 700;
            font-size: 14px;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .report-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            thead th {
                background-color: #00704a !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tfoot td {
                background-color: #ecfdf5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="report-container">
        <div class="no-print">
            <button type="button" class="btn btn-print" onclick="window.print()">Cetak Laporan</button>
            <a href="list.php" class="btn btn-back">Kembali</a>
        </div>

        <div class="report-header">
            <h1>LAPORAN TRANSAKSI PENJUALAN STARBUCKS</h1>
            <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?> WITA</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Waktu (WITA)</th>
                    <th>Menu</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Pajak (11%)</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data transaksi.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td class="text-center">#<?php echo str_pad(htmlspecialchars($row['id_transaksi']), 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($row['waktu']); ?> WITA</td>
                        <td><?php echo !empty($row['nama_produk']) ? htmlspecialchars($row['nama_produk']) : '-'; ?></td>
                        <td class="text-center"><?php echo isset($row['jumlah_beli']) && $row['jumlah_beli'] !== null ? (int)$row['jumlah_beli'] : '-'; ?></td>
                        <td class="text-end">Rp <?php echo number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                        <td class="text-end">Rp <?php echo number_format((float)$row['pajak'], 0, ',', '.'); ?></td>
                        <td class="text-end">Rp <?php echo number_format((float)$row['total'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" class="text-end">Total Keseluruhan Pendapatan</td>
                    <td class="text-end">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>

