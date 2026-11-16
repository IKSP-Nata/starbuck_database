<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/config/config.php';

$id_transaksi = isset($_GET['id_transaksi']) ? trim($_GET['id_transaksi']) : '';
$trx = null;
$items = [];

if ($id_transaksi !== '') {
    $id_esc = mysqli_real_escape_string($koneksi, $id_transaksi);

    $query = "SELECT t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu,
                     COALESCE(k.nama_kasir, '-') AS nama_kasir,
                     dt.kuantitas, p.nama_produk, p.harga AS harga_satuan
              FROM transaksi t
              LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
              LEFT JOIN kasir k ON dt.id_kasir = k.id_kasir
              LEFT JOIN produk p ON dt.id_produk = p.id_produk
              WHERE t.id_transaksi = '$id_esc'";

    $result = mysqli_query($koneksi, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($trx === null) {
                $trx = [
                    'id_transaksi' => $row['id_transaksi'],
                    'nama_kasir'   => $row['nama_kasir'],
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
                    'kuantitas'    => (int)$row['kuantitas'],
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
    <title>Struk Transaksi <?php echo $trx ? '#' . htmlspecialchars($trx['id_transaksi']) : ''; ?> - Starbuck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --bg-canvas: #F8F6F3;
            --bg-surface: #FFFFFF;
            --bg-subtle: #F3EFEA;
            --primary-coffee: #4E342E;
            --primary-hover: #3B2621;
            --text-main: #2C221E;
            --text-muted: #7A6F69;
            --border-soft: #EAE4DD;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 16px;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        .no-print {
            display: flex;
            gap: 8px;
            width: 100%;
            max-width: 320px;
            margin-bottom: 16px;
        }
        .btn-action {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 150ms ease;
        }
        .btn-print {
            color: #FFFFFF;
            background-color: var(--primary-coffee);
            border: 1px solid var(--primary-coffee);
        }
        .btn-print:hover {
            background-color: var(--primary-hover);
        }
        .btn-back {
            color: var(--primary-coffee);
            background-color: var(--bg-surface);
            border: 1px solid var(--primary-coffee);
        }
        .btn-back:hover {
            background-color: var(--bg-subtle);
        }
        .receipt {
            width: 100%;
            max-width: 320px;
            background: var(--bg-surface);
            padding: 24px 20px;
            border-radius: 18px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(44, 34, 30, 0.04);
            font-size: 13px;
            line-height: 1.5;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 14px;
        }
        .brand {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--primary-coffee);
        }
        .sub-brand {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .divider {
            border: none;
            border-top: 1px dashed var(--border-soft);
            margin: 12px 0;
        }
        .meta-row,
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 4px;
        }
        .meta-label,
        .total-label {
            color: var(--text-muted);
        }
        .meta-value,
        .total-value {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            text-align: right;
        }
        .item-block {
            margin-bottom: 10px;
        }
        .item-block:last-child {
            margin-bottom: 0;
        }
        .item-name {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 2px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
        }
        .item-subtotal {
            font-weight: 600;
            color: var(--text-main);
        }
        .grand-total {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            padding-top: 2px;
        }
        .grand-total .total-label {
            color: var(--text-main);
            font-weight: 700;
        }
        .grand-total .total-value {
            font-size: 18px;
            font-weight: 700;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 14px;
            font-size: 12px;
            color: var(--text-muted);
        }
        .not-found {
            text-align: center;
            padding: 20px 10px;
            color: var(--text-muted);
        }
        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm;
            }
            body {
                background: #FFFFFF;
                padding: 0;
                min-height: auto;
            }
            .no-print,
            .sb-sidebar,
            .sb-topbar {
                display: none !important;
            }
            .receipt {
                max-width: 100%;
                box-shadow: none;
                border: none;
                border-radius: 0;
                padding: 0;
            }
            .divider {
                border-top-color: #999999;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <?php if ($trx): ?>
        <button type="button" class="btn-action btn-print" onclick="window.print()">Cetak Struk</button>
        <?php endif; ?>
        <a href="list.php" class="btn-action btn-back">Kembali</a>
    </div>

    <div class="receipt">
        <?php if (!$trx): ?>
            <div class="not-found">
                <p>Data transaksi tidak ditemukan.</p>
            </div>
        <?php else: ?>
            <div class="receipt-header">
                <div class="brand">Starbuck</div>
                <div class="sub-brand">Struk Pembayaran Resmi</div>
            </div>

            <hr class="divider">

            <div class="meta-row">
                <span class="meta-label">No. Transaksi</span>
                <span class="meta-value">#<?php echo str_pad(htmlspecialchars($trx['id_transaksi']), 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Kasir</span>
                <span class="meta-value"><?php echo htmlspecialchars($trx['nama_kasir']); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Tanggal</span>
                <span class="meta-value"><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Waktu</span>
                <span class="meta-value"><?php echo htmlspecialchars($trx['waktu']); ?> WITA</span>
            </div>

            <hr class="divider">

            <?php if (empty($items)): ?>
                <div class="item-block">
                    <div class="item-name">Detail item tidak tersedia</div>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <?php $line_total = $item['harga_satuan'] * $item['kuantitas']; ?>
                    <div class="item-block">
                        <div class="item-name"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                        <div class="item-detail">
                            <span><?php echo (int)$item['kuantitas']; ?> &times; Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></span>
                            <span class="item-subtotal">Rp <?php echo number_format($line_total, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <hr class="divider">

            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-value">Rp <?php echo number_format($trx['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <div class="total-row">
                <span class="total-label">Pajak (11%)</span>
                <span class="total-value">Rp <?php echo number_format($trx['pajak'], 0, ',', '.'); ?></span>
            </div>

            <hr class="divider">

            <div class="total-row grand-total">
                <span class="total-label">Total</span>
                <span class="total-value">Rp <?php echo number_format($trx['total'], 0, ',', '.'); ?></span>
            </div>

            <hr class="divider">

            <div class="receipt-footer">
                <div>Terima kasih atas kunjungan Anda.</div>
                <div>Simpan struk ini sebagai bukti pembayaran.</div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
