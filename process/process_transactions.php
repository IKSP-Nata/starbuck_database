<?php
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'add')) {
    $id_produk   = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $id_kasir    = isset($_POST['id_kasir']) ? (int)$_POST['id_kasir'] : 0;
    $kuantitas   = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
    $catatan_raw = trim($_POST['catatan'] ?? '');

    if ($id_produk <= 0 || $id_kasir <= 0 || $kuantitas < 1) {
        setFlashToast('danger', 'Pilih menu, kasir bertugas, dan jumlah beli yang valid.');
        header('Location: ../pages/transactions/add.php');
        exit;
    }

    // Validasi keberadaan kasir bertugas di database
    $stmt_kasir = mysqli_prepare($koneksi, "SELECT id_kasir FROM kasir WHERE id_kasir = ? LIMIT 1");
    $kasir_valid = false;
    if ($stmt_kasir) {
        mysqli_stmt_bind_param($stmt_kasir, 'i', $id_kasir);
        mysqli_stmt_execute($stmt_kasir);
        $res_kasir = mysqli_stmt_get_result($stmt_kasir);
        $kasir_valid = ($res_kasir && mysqli_num_rows($res_kasir) > 0);
        mysqli_stmt_close($stmt_kasir);
    }

    if (!$kasir_valid) {
        setFlashToast('danger', 'Data kasir bertugas tidak ditemukan.');
        header('Location: ../pages/transactions/add.php');
        exit;
    }

    // Deteksi nama kolom primary key pada tabel produk (id_produk atau id)
    $col_id_produk = 'id';
    $check_col_produk = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
    if ($check_col_produk && mysqli_num_rows($check_col_produk) > 0) {
        $col_id_produk = 'id_produk';
    }

    // Ambil harga asli dan kategori langsung dari tabel produk
    $stmt_produk = mysqli_prepare($koneksi, "SELECT harga, kategori FROM produk WHERE $col_id_produk = ? LIMIT 1");
    $row_produk = null;
    if ($stmt_produk) {
        mysqli_stmt_bind_param($stmt_produk, 'i', $id_produk);
        mysqli_stmt_execute($stmt_produk);
        $res_produk = mysqli_stmt_get_result($stmt_produk);
        if ($res_produk) {
            $row_produk = mysqli_fetch_assoc($res_produk);
        }
        mysqli_stmt_close($stmt_produk);
    }

    if (!$row_produk) {
        setFlashToast('danger', 'Data produk tidak ditemukan.');
        header('Location: ../pages/transactions/add.php');
        exit;
    }

    $harga    = (float)($row_produk['harga'] ?? 0);
    $kategori = trim($row_produk['kategori'] ?? 'Minuman');
    if (!in_array($kategori, ['Minuman', 'Tambahan', 'Makanan'], true)) {
        $kategori = 'Minuman';
    }

    // Validasi catatan: hanya disimpan jika kategori === 'Minuman' dan sesuai daftar suhu & gula yang sah
    $catatan = null;
    if ($kategori === 'Minuman' && $catatan_raw !== '') {
        $parts = array_map('trim', explode(',', $catatan_raw));
        $allowed_suhu = ['Ice', 'Hot'];
        $allowed_gula = ['No sugar', 'Less sugar', 'Normal', 'High sugar'];
        if (
            count($parts) === 2 &&
            in_array($parts[0], $allowed_suhu, true) &&
            in_array($parts[1], $allowed_gula, true)
        ) {
            $catatan = $parts[0] . ', ' . $parts[1];
        }
    }

    // Perhitungan ulang seluruh nominal di sisi server
    $kuantitas_harga = round($harga * $kuantitas);
    $subtotal        = $kuantitas_harga;
    $pajak           = round($subtotal * 0.11);
    $total           = $subtotal + $pajak;
    $tanggal         = date('Y-m-d');
    $waktu           = date('H:i:s');

    mysqli_begin_transaction($koneksi);

    try {
        // Buat ID transaksi baru menggunakan angka urut MAX(id_transaksi) + 1
        $max_result   = mysqli_query($koneksi, "SELECT IFNULL(MAX(id_transaksi), 0) + 1 AS next_id FROM transaksi");
        $max_row      = $max_result ? mysqli_fetch_assoc($max_result) : null;
        $id_transaksi = (int)($max_row['next_id'] ?? 1);

        $stmt_trx = mysqli_prepare(
            $koneksi,
            "INSERT INTO transaksi (id_transaksi, subtotal, pajak, total, tanggal, waktu) VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt_trx) {
            throw new Exception(mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmt_trx, 'idddss', $id_transaksi, $subtotal, $pajak, $total, $tanggal, $waktu);
        if (!mysqli_stmt_execute($stmt_trx)) {
            $err = mysqli_stmt_error($stmt_trx);
            mysqli_stmt_close($stmt_trx);
            throw new Exception($err);
        }
        mysqli_stmt_close($stmt_trx);

        // Buat ID detail_transaksi baru menggunakan MAX(id_detail_transaksi) + 1
        $max_det_res = mysqli_query($koneksi, "SELECT IFNULL(MAX(id_detail_transaksi), 0) + 1 AS next_id FROM detail_transaksi");
        $max_det_row = $max_det_res ? mysqli_fetch_assoc($max_det_res) : null;
        $id_detail   = (int)($max_det_row['next_id'] ?? 1);

        $stmt_det = mysqli_prepare(
            $koneksi,
            "INSERT INTO detail_transaksi (id_detail_transaksi, id_kasir, id_produk, id_transaksi, kategori, kuantitas, kuantitas_harga, catatan, harga)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt_det) {
            throw new Exception(mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param(
            $stmt_det,
            'iiiisidsd',
            $id_detail,
            $id_kasir,
            $id_produk,
            $id_transaksi,
            $kategori,
            $kuantitas,
            $kuantitas_harga,
            $catatan,
            $harga
        );
        if (!mysqli_stmt_execute($stmt_det)) {
            $err = mysqli_stmt_error($stmt_det);
            mysqli_stmt_close($stmt_det);
            throw new Exception($err);
        }
        mysqli_stmt_close($stmt_det);

        mysqli_commit($koneksi);

        setFlashToast('success', 'Transaksi berhasil disimpan.');
        header('Location: ../pages/transactions/list.php');
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        setFlashToast('danger', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        header('Location: ../pages/transactions/add.php');
        exit;
    }
} else {
    header('Location: ../pages/transactions/list.php');
    exit;
}
?>
