<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/config/config.php';

$filter           = Transaksi::buatFilter($_GET);
$query_string     = $_SERVER['QUERY_STRING'] ?? '';
$back_url         = 'list.php' . ($query_string !== '' ? '?' . htmlspecialchars($query_string, ENT_QUOTES, 'UTF-8') : '');
$riwayat          = Transaksi::ambilRiwayat($filter);
$rows             = $riwayat['rows'];
$total_pendapatan = $riwayat['total_pendapatan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Penjualan - Starbuck</title>
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
            --accent-soft: #D4E9E2;
            --text-primary: #2C2A29;
            --text-muted: #6B6661;
            --font-sans: 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: var(--font-sans);
            background-color: var(--bg-main);
            color: var(--text-primary);
            margin: 0;
            padding: 32px 20px;
            -webkit-font-smoothing: antialiased;
        }
        button,
        input,
        select,
        textarea,
        table {
            font-family: var(--font-sans);
        }
        .report-container {
            max-width: 1020px;
            margin: 0 auto;
            background: var(--bg-surface);
            padding: 32px;
            border-radius: 16px;
            border: 1px solid var(--border-soft);
            box-shadow: none;
        }
        .no-print {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 24px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 22px;
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
            background-color: transparent;
            border: 1px solid var(--primary-brand);
        }
        .btn-back:hover {
            background-color: rgba(0, 117, 74, 0.08);
        }
        .report-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            border-bottom: 1px solid var(--border-soft);
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }
        .report-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: var(--text-muted);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        th, td {
            border-bottom: 1px solid var(--border-soft);
            padding: 12px 14px;
            text-align: left;
            vertical-align: middle;
        }
        thead th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            background-color: var(--bg-subtle);
            border-top: 1px solid var(--border-soft);
        }
        tfoot td {
            background-color: var(--bg-subtle);
            font-weight: 700;
            font-size: 15px;
            border-top: 2px solid var(--primary-brand);
            border-bottom: none;
            padding-top: 14px;
            padding-bottom: 14px;
        }
        .note-badge {
            display: inline-block;
            margin-top: 3px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary-brand);
            background-color: var(--accent-soft);
            border-radius: 50px;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .no-print,
            .sb-sidebar,
            .sb-topbar {
                display: none !important;
            }
            .report-container {
                box-shadow: none;
                border: none;
                border-radius: 0;
                padding: 0;
                max-width: 100%;
            }
            thead th,
            tfoot td {
                background-color: #EFECE6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="report-container">
        <div class="no-print">
            <button type="button" class="btn-action btn-print" onclick="window.print()">Cetak Laporan</button>
            <a href="<?php echo $back_url; ?>" class="btn-action btn-back">Kembali</a>
        </div>

        <div class="report-header">
            <div>
                <h1>Laporan Transaksi Penjualan</h1>
                <p>Periode Laporan: <strong><?php echo htmlspecialchars($filter['periode_label'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
            </div>
            <div class="tabular-nums" style="font-size: 13px; color: var(--text-muted);">
                Dicetak: <?php echo date('d/m/Y H:i:s'); ?> WITA
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 48px;">No</th>
                    <th>Tanggal</th>
                    <th>Waktu (WITA)</th>
                    <th>Kasir</th>
                    <th>Menu</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Pajak (11%)</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 24px; color: var(--text-muted);">
                            Belum ada data transaksi yang tercatat pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-center tabular-nums"><?php echo $no++; ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars(formatTanggalIndo($row['tanggal']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="tabular-nums"><?php echo htmlspecialchars($row['waktu'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kasir'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (empty($row['items'])): ?>
                                -
                            <?php else: ?>
                                <?php $is_multi = count($row['items']) > 1; ?>
                                <?php foreach ($row['items'] as $idx_item => $item): ?>
                                    <div style="<?php echo $idx_item > 0 ? 'margin-top: 6px;' : ''; ?>">
                                        <div>
                                            <?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if ($is_multi): ?>
                                                <span class="tabular-nums" style="color: var(--text-muted);">(x<?php echo (int)$item['kuantitas']; ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($item['catatan'] !== ''): ?>
                                            <div><span class="note-badge"><?php echo htmlspecialchars($item['catatan'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end tabular-nums"><?php echo $row['total_kuantitas'] !== null ? (int)$row['total_kuantitas'] : '-'; ?></td>
                        <td class="text-end tabular-nums">Rp <?php echo number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                        <td class="text-end tabular-nums">Rp <?php echo number_format((float)$row['pajak'], 0, ',', '.'); ?></td>
                        <td class="text-end tabular-nums" style="font-weight: 700;">Rp <?php echo number_format((float)$row['total'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" class="text-end">Total Pendapatan</td>
                    <td class="text-end tabular-nums">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>
