<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$id = $_GET['id_transaksi'] ?? '';
$query = "SELECT * FROM transaksi WHERE id_transaksi = '$id'";
$result = mysqli_query($koneksi, $query);
$row = mysqli_fetch_assoc($result);
?>

<div class="container-fluid px-0">
    <div class="card-soft card-narrow mt-2">
        <div class="mb-4">
            <h1 class="page-title">Detail Transaksi</h1>
            <p class="page-subtitle">Riwayat transaksi bersifat tetap dan hanya dapat dicetak</p>
        </div>

        <form action="../../process/process_transactions.php" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_transaksi" value="<?php echo htmlspecialchars($row['id_transaksi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
                <label for="subtotal" class="form-label fw-medium">Subtotal (Rp)</label>
                <input type="number" name="subtotal"
                    value="<?php echo htmlspecialchars($row['subtotal'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="subtotal" readonly>
            </div>

            <div class="mb-3">
                <label for="pajak" class="form-label fw-medium">Pajak 11% (Rp)</label>
                <input type="number" name="pajak"
                    value="<?php echo htmlspecialchars($row['pajak'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="pajak" readonly>
            </div>

            <div class="mb-3">
                <label for="total" class="form-label fw-medium">Total (Rp)</label>
                <input type="number" name="total"
                    value="<?php echo htmlspecialchars($row['total'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="total" readonly>
            </div>

            <div class="mb-3">
                <label for="tanggal" class="form-label fw-medium">Tanggal</label>
                <input type="date" name="tanggal"
                    value="<?php echo htmlspecialchars($row['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="tanggal" readonly>
            </div>

            <div class="mb-4">
                <label for="waktu" class="form-label fw-medium">Waktu (WITA)</label>
                <input type="time" step="1" name="waktu"
                    value="<?php echo htmlspecialchars($row['waktu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control tabular-nums" id="waktu" readonly>
            </div>

            <div class="d-flex gap-2">
                <a href="list.php" class="btn-coffee-outline">Kembali ke Riwayat</a>
            </div>
        </form>
    </div>
</div>

<?php
include ROOTPATH . '/include/footer.php';
?>
