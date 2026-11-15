<?php
include '../config/config.php';

// Hanya memproses penambahan transaksi baru (logika edit dan hapus dinonaktifkan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'add')) {
    $id_produk = trim($_POST['id_produk'] ?? '');
    $jumlah    = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

    // Validasi input utama (hanya id_produk dan jumlah)
    if ($id_produk === '' || $jumlah <= 0) {
        echo "<script>
        alert('Produk dan jumlah wajib diisi dengan benar!');
        window.location.href='../pages/transactions/add.php';
        </script>";
        exit;
    }

    // Deteksi nama kolom primary key pada tabel produk (id_produk atau id)
    $col_id_produk = 'id';
    $check_col_produk = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
    if ($check_col_produk && mysqli_num_rows($check_col_produk) > 0) {
        $col_id_produk = 'id_produk';
    }

    // Ambil harga produk langsung dari tabel produk berdasarkan id_produk (tanpa mengurangi stok)
    $id_produk_esc = mysqli_real_escape_string($koneksi, $id_produk);
    $res_produk    = mysqli_query($koneksi, "SELECT harga FROM produk WHERE $col_id_produk = '$id_produk_esc' LIMIT 1");

    if (!$res_produk || mysqli_num_rows($res_produk) === 0) {
        echo "<script>
        alert('Data produk tidak ditemukan!');
        window.location.href='../pages/transactions/add.php';
        </script>";
        exit;
    }

    $row_produk = mysqli_fetch_assoc($res_produk);
    $harga      = (float)($row_produk['harga'] ?? 0);

    // Perhitungan otomatis di sisi server
    $subtotal = $harga * $jumlah;
    $pajak    = $subtotal * 0.11; // Pajak dikunci 11%
    $total    = $subtotal + $pajak;
    $tanggal  = date('Y-m-d');
    $waktu    = date('H:i:s');

    // Reset AUTO_INCREMENT tabel transaksi agar mengikuti ID terakhir
    $max_result = mysqli_query($koneksi, "SELECT IFNULL(MAX(id_transaksi), 0) + 1 AS next_id FROM transaksi");
    $max_row    = $max_result ? mysqli_fetch_assoc($max_result) : null;
    $next_id    = (int)($max_row['next_id'] ?? 1);
    mysqli_query($koneksi, "ALTER TABLE transaksi AUTO_INCREMENT = $next_id");

    // Cek apakah kolom id_kasir ada di tabel transaksi
    $check_kasir_col = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi LIKE 'id_kasir'");
    if ($check_kasir_col && mysqli_num_rows($check_kasir_col) > 0) {
        $col_kasir_info = mysqli_fetch_assoc($check_kasir_col);
        $is_kasir_not_null = (($col_kasir_info['Null'] ?? 'NO') === 'NO');

        // Ambil otomatis id_kasir pertama yang tersedia dari tabel kasir sebagai default sementara
        $res_kasir = mysqli_query($koneksi, "SELECT id_kasir FROM kasir ORDER BY id_kasir ASC LIMIT 1");
        $row_kasir = ($res_kasir && mysqli_num_rows($res_kasir) > 0) ? mysqli_fetch_assoc($res_kasir) : null;

        if ($row_kasir) {
            $id_kasir = mysqli_real_escape_string($koneksi, $row_kasir['id_kasir']);
            $query_transaksi = "INSERT INTO transaksi (id_kasir, subtotal, pajak, total, tanggal, waktu) VALUES ('$id_kasir', '$subtotal', '$pajak', '$total', '$tanggal', '$waktu')";
        } else if ($is_kasir_not_null) {
            echo "<script>
            alert('Gagal menyimpan transaksi: Belum ada data kasir di tabel kasir. Silakan tambahkan data kasir terlebih dahulu.');
            window.location.href='../pages/transactions/add.php';
            </script>";
            exit;
        } else {
            $query_transaksi = "INSERT INTO transaksi (subtotal, pajak, total, tanggal, waktu) VALUES ('$subtotal', '$pajak', '$total', '$tanggal', '$waktu')";
        }
    } else {
        $query_transaksi = "INSERT INTO transaksi (subtotal, pajak, total, tanggal, waktu) VALUES ('$subtotal', '$pajak', '$total', '$tanggal', '$waktu')";
    }

    // Simpan data ke tabel transaksi
    $hasil_transaksi = mysqli_query($koneksi, $query_transaksi);

    if (!$hasil_transaksi) {
        echo "<script>
        alert('Data transaksi gagal ditambahkan: " . addslashes(mysqli_error($koneksi)) . "');
        window.location.href='../pages/transactions/add.php';
        </script>";
        exit;
    }

    $id_transaksi = mysqli_insert_id($koneksi);
    if (!$id_transaksi) {
        $id_transaksi = $next_id;
    }

    // Simpan relasi ke tabel detail_transaksi dengan menyesuaikan kolom yang tersedia di database
    $detail_cols = [];
    $pk_detail   = null;
    $res_detail_cols = mysqli_query($koneksi, "SHOW COLUMNS FROM detail_transaksi");
    if ($res_detail_cols) {
        while ($col = mysqli_fetch_assoc($res_detail_cols)) {
            $field = $col['Field'];
            if (stripos($col['Extra'] ?? '', 'auto_increment') !== false) {
                $pk_detail = $field;
                continue;
            }
            $detail_cols[$field] = $col;
        }
    }

    if ($pk_detail) {
        $max_det_res = mysqli_query($koneksi, "SELECT IFNULL(MAX($pk_detail), 0) + 1 AS next_id FROM detail_transaksi");
        $max_det_row = $max_det_res ? mysqli_fetch_assoc($max_det_res) : null;
        $next_det_id = (int)($max_det_row['next_id'] ?? 1);
        mysqli_query($koneksi, "ALTER TABLE detail_transaksi AUTO_INCREMENT = $next_det_id");
    }

    $insert_cols = [];
    $insert_vals = [];

    if (!empty($detail_cols)) {
        foreach ($detail_cols as $col_name => $col_meta) {
            if ($col_name === 'id_transaksi') {
                $insert_cols[] = 'id_transaksi';
                $insert_vals[] = "'$id_transaksi'";
            } else if ($col_name === 'id_produk') {
                $insert_cols[] = 'id_produk';
                $insert_vals[] = "'$id_produk_esc'";
            } else if (in_array($col_name, ['jumlah', 'qty', 'kuantitas'], true)) {
                $insert_cols[] = $col_name;
                $insert_vals[] = "'$jumlah'";
            } else if (in_array($col_name, ['harga', 'harga_satuan'], true)) {
                $insert_cols[] = $col_name;
                $insert_vals[] = "'$harga'";
            } else if (in_array($col_name, ['subtotal', 'total_harga', 'total'], true)) {
                $insert_cols[] = $col_name;
                $insert_vals[] = "'$subtotal'";
            } else if ($col_name === 'pajak') {
                $insert_cols[] = 'pajak';
                $insert_vals[] = "'$pajak'";
            } else if (($col_meta['Key'] ?? '') === 'PRI') {
                // Jika primary key detail_transaksi bukan auto_increment
                $max_det_res = mysqli_query($koneksi, "SELECT IFNULL(MAX($col_name), 0) + 1 AS next_id FROM detail_transaksi");
                $max_det_row = $max_det_res ? mysqli_fetch_assoc($max_det_res) : null;
                $next_det_id = (int)($max_det_row['next_id'] ?? 1);
                $insert_cols[] = $col_name;
                $insert_vals[] = "'$next_det_id'";
            }
        }
        $query_detail = "INSERT INTO detail_transaksi (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $insert_vals) . ")";
    } else {
        // Default standar jika struktur kolom tidak terbaca
        $query_detail = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah) VALUES ('$id_transaksi', '$id_produk_esc', '$jumlah')";
    }

    $hasil_detail = mysqli_query($koneksi, $query_detail);

    if ($hasil_detail) {
        echo "<script>
        alert('Transaksi berhasil ditambahkan!');
        window.location.href='../pages/transactions/list.php';
        </script>";
    } else {
        // Batalkan data transaksi utama jika gagal menyimpan ke detail_transaksi
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi = '$id_transaksi'");
        echo "<script>
        alert('Gagal menyimpan detail transaksi: " . addslashes(mysqli_error($koneksi)) . "');
        window.location.href='../pages/transactions/add.php';
        </script>";
    }
} else {
    header("Location: ../pages/transactions/list.php");
    exit;
}
?>


