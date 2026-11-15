<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/config/config.php';

$id_transaksi = isset($_GET['id_transaksi']) ? trim($_GET['id_transaksi']) : '';
$trx = null;
$items = [];

if ($id_transaksi !== '') {
    $id_esc = mysqli_real_escape_string($koneksi, $id_transaksi);

    // Deteksi nama kolom primary key pada tabel produk (id_produk atau id)
    $col_id_produk = 'id';
    $check_prod_pk = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
    if ($check_prod_pk && mysqli_num_rows($check_prod_pk) > 0) {
        $col_id_produk = 'id_produk';
    }

    // Deteksi nama kolom jumlah dan harga pada tabel detail_transaksi
    $col_jumlah = 'jumlah';
    $col_harga_detail = null;
    $res_dt_cols = mysqli_query($koneksi, "SHOW COLUMNS FROM detail_transaksi");
    if ($res_dt_cols) {
        while ($col = mysqli_fetch_assoc($res_dt_cols)) {
            $field = $col['Field'];
            if (in_array($field, ['jumlah', 'qty', 'kuantitas'], true)) {
                $col_jumlah = $field;
            } else if (in_array($field, ['harga', 'harga_satuan'], true)) {
                $col_harga_detail = $field;
            }
        }
    }

    $select_harga = $col_harga_detail
        ? "COALESCE(dt.$col_harga_detail, p.harga) AS harga_satuan"
        : "p.harga AS harga_satuan";

    $query = "SELECT t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu,
                     dt.$col_jumlah AS jumlah_beli, p.nama_produk, $select_harga
              FROM transaksi t
              LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
              LEFT JOIN produk p ON dt.id_produk = p.$col_id_produk
              WHERE t.id_transaksi = '$id_esc'";

    $result = mysqli_query($koneksi, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($trx === null) {
                $trx = [
                    'id_transaksi' => $row['id_transaksi'],
                    'subtotal'     => (float)$row['subtotal'],
                    'pajak'        => (float)$row['pajak'],
                    'total'        => (float)$row['total'],
                    'tanggal'      => $row['tanggal'],
                    'waktu'        => $row['waktu'],
                ];
            }
            if (!empty($row['nama_produk'])) {
                $items[] = [
                    'nama_produk'  => $row['nama_produk'],
                    'harga_satuan' => (float)$row['harga_satuan'],
                    'jumlah_beli'  => (int)$row['jumlah_beli'],
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi <?php echo $trx ? '#' . htmlspecialchars($trx['id_transaksi']) : ''; ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #1a1d20;
            color: #111;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px;
            min-height: 100vh;
        }
        .no-print {
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
        }
        .btn {
            font-family: system-ui, -apple-system, sans-serif;
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
            background-color: #495057;
            color: #fff;
        }
        .receipt {
            width: 80mm;
            max-width: 100%;
            background: #fff;
            padding: 18px 14px;
            border-radius: 4px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            font-size: 12px;
            line-height: 1.45;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .brand {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #00704a;
        }
        .sub-brand {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .divider {
            border: none;
            border-top: 1px dashed #333;
            margin: 10px 0;
        }
        .meta-row,
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 3px;
        }
        .item-block {
            margin-bottom: 8px;
        }
        .item-name {
            font-weight: 700;
            margin-bottom: 2px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            color: #222;
        }
        .grand-total {
            font-size: 14px;
            font-weight: 700;
            margin-top: 4px;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 12px;
            font-size: 11px;
            color: #444;
        }
        .not-found {
            text-align: center;
            padding: 20px 10px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm;
            }
            body {
                background: #fff;
                padding: 0;
                display: block;
            }
            .no-print {
                display: none !important;
            }
            .receipt {
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <?php if ($trx): ?>
        <button type="button" class="btn btn-print" onclick="window.print()">Cetak Struk</button>
        <?php endif; ?>
        <a href="list.php" class="btn btn-back">Kembali</a>
    </div>

    <div class="receipt">
        <?php if (!$trx): ?>
            <div class="not-found">
                <p><strong>Data transaksi tidak ditemukan.</strong></p>
            </div>
        <?php else: ?>
            <div class="receipt-header">
                <div class="brand">STARBUCKS</div>
                <div class="sub-brand">Coffee &amp; Beverages</div>
            </div>

            <hr class="divider">

            <div class="meta-row">
                <span>No. Transaksi</span>
                <span>#<?php echo str_pad(htmlspecialchars($trx['id_transaksi']), 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="meta-row">
                <span>Tanggal</span>
                <span><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></span>
            </div>
            <div class="meta-row">
                <span>Waktu</span>
                <span><?php echo htmlspecialchars($trx['waktu']); ?> WITA</span>
            </div>

            <hr class="divider">

            <?php if (empty($items)): ?>
                <div class="item-block">
                    <div class="item-name">Detail item tidak tersedia</div>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <?php $line_total = $item['harga_satuan'] * $item['jumlah_beli']; ?>
                    <div class="item-block">
                        <div class="item-name"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                        <div class="item-detail">
                            <span><?php echo $item['jumlah_beli']; ?> x Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></span>
                            <span>Rp <?php echo number_format($line_total, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <hr class="divider">

            <div class="total-row">
                <span>Subtotal</span>
                <span>Rp <?php echo number_format($trx['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <div class="total-row">
                <span>Pajak (11%)</span>
                <span>Rp <?php echo number_format($trx['pajak'], 0, ',', '.'); ?></span>
            </div>
            <hr class="divider">
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span>Rp <?php echo number_format($trx['total'], 0, ',', '.'); ?></span>
            </div>

            <hr class="divider">

            <div class="receipt-footer">
                <div>Terima kasih atas kunjungan Anda!</div>
                <div>Simpan struk ini sebagai bukti pembayaran.</div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

