<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/config/config.php';

$id_transaksi = isset($_GET['id_transaksi']) ? (int)$_GET['id_transaksi'] : 0;
$trx   = Transaksi::cariDetail($id_transaksi);
$items = $trx['items'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi <?php echo $trx ? '#' . htmlspecialchars((string)$trx['id_transaksi'], ENT_QUOTES, 'UTF-8') : ''; ?> - Starbuck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --bg-main: #F7F5F0;
            --bg-surface: #FFFFFF;
            --bg-subtle: #EFECE6;
            --border-soft: #E5E0D8;
            --primary-brand: #1E3932;
            --primary-brand-hover: #006241;
            --accent-active: #00754A;
            --text-primary: #2C2A29;
            --text-muted: #6B6661;
            --font-sans: 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: var(--font-sans);
            background-color: var(--bg-main);
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 16px;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        button,
        input,
        select,
        textarea,
        table {
            font-family: var(--font-sans);
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
            padding: 0 18px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease;
        }
        .btn-print {
            color: #FFFFFF;
            background-color: var(--primary-brand);
            border: 1px solid var(--primary-brand);
        }
        .btn-print:hover {
            background-color: var(--primary-brand-hover);
            border-color: var(--primary-brand-hover);
        }
        .btn-back {
            color: var(--primary-brand);
            background-color: var(--bg-surface);
            border: 1px solid var(--primary-brand);
        }
        .btn-back:hover {
            background-color: rgba(0, 117, 74, 0.08);
        }
        .receipt {
            width: 100%;
            max-width: 320px;
            background: var(--bg-surface);
            padding: 24px 20px;
            border-radius: 16px;
            border: 1px solid var(--border-soft);
            box-shadow: none;
            font-size: 13px;
            line-height: 1.5;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 14px;
        }
        .receipt-logo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid var(--border-soft);
            display: block;
            margin: 0 auto 8px;
        }
        .brand {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--primary-brand);
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
            color: var(--text-primary);
            margin-bottom: 2px;
        }
        .item-note {
            font-size: 12px;
            color: var(--text-muted);
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
            color: var(--text-primary);
        }
        .grand-total {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            padding-top: 2px;
        }
        .grand-total .total-label {
            color: var(--text-primary);
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
                <img src="../../assets/img/logo-starbuck.png" alt="Starbuck" class="receipt-logo" onerror="this.style.display='none';">
                <div class="brand">Starbuck POS</div>
                <div class="sub-brand">Struk Pembayaran Resmi</div>
            </div>

            <hr class="divider">

            <div class="meta-row">
                <span class="meta-label">No. Nota</span>
                <span class="meta-value">#<?php echo str_pad(htmlspecialchars((string)$trx['id_transaksi'], ENT_QUOTES, 'UTF-8'), 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Kasir</span>
                <span class="meta-value"><?php echo htmlspecialchars($trx['nama_kasir'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Tanggal</span>
                <span class="meta-value"><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Waktu</span>
                <span class="meta-value"><?php echo htmlspecialchars($trx['waktu'], ENT_QUOTES, 'UTF-8'); ?> WITA</span>
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
                        <div class="item-name"><?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php if ($item['catatan'] !== ''): ?>
                            <div class="item-note">Catatan: <?php echo htmlspecialchars($item['catatan'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
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

    <?php if ($trx && isset($_GET['autoprint'])): ?>
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
    <?php endif; ?>

</body>
</html>
