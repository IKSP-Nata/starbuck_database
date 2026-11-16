<?php
include '../config/config.php';

// Deteksi nama kolom primary key (id atau id_produk)
$col_id = 'id';
$check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
if ($check_col && mysqli_num_rows($check_col) > 0) {
    $col_id = 'id_produk';
}

$upload_dir = '../assets/img/products/';

// Pastikan folder penyimpanan foto produk tersedia
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/**
 * Validasi dan simpan file foto produk yang diunggah.
 * Mengembalikan nama file baru, atau null jika tidak ada file yang diunggah.
 */
function uploadFotoProduk($file, $upload_dir, $redirect_url)
{
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== 0) {
        echo "<script>
        alert('Gagal mengunggah foto produk. Silakan coba lagi.');
        window.location.href='" . $redirect_url . "';
        </script>";
        exit;
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $original_name = basename($file['name']);
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext, true)) {
        echo "<script>
        alert('Format foto tidak valid! Gunakan file JPG, JPEG, PNG, atau WEBP.');
        window.location.href='" . $redirect_url . "';
        </script>";
        exit;
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);
    $new_filename = time() . '_' . $safe_name;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        echo "<script>
        alert('Gagal menyimpan file foto ke server.');
        window.location.href='" . $redirect_url . "';
        </script>";
        exit;
    }

    return $new_filename;
}

if (isset($_POST["action"]) && $_POST["action"] == "add") {
    $nama_produk = $_POST['nama_produk'] ?? '';
    $harga       = $_POST['harga'] ?? '';

    // Validasi agar tidak bisa submit jika data masih kosong
    if (empty(trim($nama_produk)) || empty(trim($harga))) {
        echo "<script>
        alert('Semua data wajib diisi! Mohon lengkapi formulir.');
        window.location.href='../pages/products/add.php';
        </script>";
        exit;
    }

    $nama_file = uploadFotoProduk($_FILES['gambar'] ?? null, $upload_dir, '../pages/products/add.php');

    // Reset AUTO_INCREMENT agar ID mengikuti data terakhir yang ada
    $max_result = mysqli_query($koneksi, "SELECT IFNULL(MAX($col_id), 0) + 1 AS next_id FROM produk");
    $max_row    = mysqli_fetch_assoc($max_result);
    $next_id    = (int)($max_row['next_id'] ?? 1);
    mysqli_query($koneksi, "ALTER TABLE produk AUTO_INCREMENT = $next_id");

    $nama_produk_esc = mysqli_real_escape_string($koneksi, $nama_produk);
    $harga_esc       = mysqli_real_escape_string($koneksi, $harga);
    $gambar_sql      = ($nama_file !== null)
        ? "'" . mysqli_real_escape_string($koneksi, $nama_file) . "'"
        : "NULL";

    // Menambahkan data produk ke tabel produk (nama_produk, harga, gambar)
    $hasil = mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, gambar) VALUES ('$nama_produk_esc', '$harga_esc', $gambar_sql)");

    if ($hasil) {
        echo "<script>
        alert('Data berhasil ditambahkan!');
        window.location.href='../pages/products/list.php';
        </script>";
    } else {
        if ($nama_file !== null && file_exists($upload_dir . $nama_file)) {
            @unlink($upload_dir . $nama_file);
        }
        echo "<script>
        alert('Data gagal ditambahkan: " . addslashes(mysqli_error($koneksi)) . "');
        window.location.href='../pages/products/add.php';
        </script>";
    }
} else if (isset($_POST["action"]) && $_POST["action"] == "edit") {
    $id          = $_POST["id"] ?? $_POST["id_produk"] ?? '';
    $nama_produk = $_POST['nama_produk'] ?? '';
    $harga       = $_POST['harga'] ?? '';
    $gambar_lama = trim($_POST['gambar_lama'] ?? '');

    // Validasi agar tidak bisa submit jika data masih kosong
    if (empty(trim($id)) || empty(trim($nama_produk)) || empty(trim($harga))) {
        echo "<script>
        alert('Semua data wajib diisi! Mohon lengkapi formulir.');
        window.location.href='../pages/products/edit.php?id=$id&id_produk=$id';
        </script>";
        exit;
    }

    $redirect_edit = '../pages/products/edit.php?id=' . urlencode($id) . '&id_produk=' . urlencode($id);
    $nama_file = uploadFotoProduk($_FILES['gambar'] ?? null, $upload_dir, $redirect_edit);

    if ($nama_file !== null) {
        if ($gambar_lama !== '' && file_exists($upload_dir . basename($gambar_lama))) {
            @unlink($upload_dir . basename($gambar_lama));
        }
        $gambar_sql = "'" . mysqli_real_escape_string($koneksi, $nama_file) . "'";
    } else {
        $gambar_sql = ($gambar_lama !== '')
            ? "'" . mysqli_real_escape_string($koneksi, $gambar_lama) . "'"
            : "NULL";
    }

    $id_esc          = mysqli_real_escape_string($koneksi, $id);
    $nama_produk_esc = mysqli_real_escape_string($koneksi, $nama_produk);
    $harga_esc       = mysqli_real_escape_string($koneksi, $harga);

    // Mengupdate data produk di tabel produk
    $hasil = mysqli_query($koneksi, "UPDATE produk SET nama_produk='$nama_produk_esc', harga='$harga_esc', gambar=$gambar_sql WHERE $col_id='$id_esc'");

    if (!$hasil) {
        echo "<script>
        alert('Gagal mengupdate data produk: " . addslashes(mysqli_error($koneksi)) . "');
        window.location.href='$redirect_edit';
        </script>";
    } else {
        echo "<script>
        alert('Data produk berhasil diupdate.');
        window.location.href='../pages/products/list.php';
        </script>";
    }

} else if (isset($_GET["action"]) && $_GET["action"] == "delete") {
    $id = $_GET["id"] ?? $_GET["id_produk"] ?? '';
    $id_esc = mysqli_real_escape_string($koneksi, $id);

    // Hapus file foto produk dari folder jika ada sebelum menghapus data dari database
    $res_foto = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE $col_id='$id_esc' LIMIT 1");
    if ($res_foto && ($row_foto = mysqli_fetch_assoc($res_foto))) {
        $foto_lama = trim($row_foto['gambar'] ?? '');
        if ($foto_lama !== '' && file_exists($upload_dir . basename($foto_lama))) {
            @unlink($upload_dir . basename($foto_lama));
        }
    }

    // Menghapus data produk di tabel produk
    $hasil = mysqli_query($koneksi, "DELETE FROM produk WHERE $col_id='$id_esc'");

    if (!$hasil) {
        echo "<script>
        alert('Gagal menghapus data produk: " . addslashes(mysqli_error($koneksi)) . "');
        window.location.href='../pages/products/list.php';
        </script>";
    } else {
        // Reset AUTO_INCREMENT agar ID berikutnya mengikuti data terakhir yang masih ada
        $max_result = mysqli_query($koneksi, "SELECT IFNULL(MAX($col_id), 0) + 1 AS next_id FROM produk");
        $max_row    = mysqli_fetch_assoc($max_result);
        $next_id    = (int)($max_row['next_id'] ?? 1);
        mysqli_query($koneksi, "ALTER TABLE produk AUTO_INCREMENT = $next_id");

        echo "<script>
        alert('Data produk berhasil dihapus.');
        window.location.href='../pages/products/list.php';
        </script>";
    }
}
?>
