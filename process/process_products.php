<?php
include '../config/config.php';

// Deteksi nama kolom primary key (id atau id_produk)
$col_id = 'id';
$check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
if ($check_col && mysqli_num_rows($check_col) > 0) {
    $col_id = 'id_produk';
}

$allowed_kategori = ['Minuman', 'Tambahan', 'Makanan'];
$upload_dir = '../assets/img/products/';

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

    if ($file['error'] !== UPLOAD_ERR_OK) {
        setFlashToast('danger', 'Gagal mengunggah foto produk. Silakan coba lagi.');
        header('Location: ' . $redirect_url);
        exit;
    }

    $max_size = 2 * 1024 * 1024; // Maksimal 2 MB sesuai PRD Bagian 7
    if (($file['size'] ?? 0) > $max_size) {
        setFlashToast('danger', 'Ukuran foto maksimal 2 MB.');
        header('Location: ' . $redirect_url);
        exit;
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
    $original_name = basename($file['name']);
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext, true)) {
        setFlashToast('danger', 'Format foto tidak valid. Gunakan file JPG, JPEG, PNG, atau WEBP.');
        header('Location: ' . $redirect_url);
        exit;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($mime !== false && !in_array($mime, $allowed_mime, true)) {
                setFlashToast('danger', 'Tipe file foto tidak diizinkan. Gunakan gambar JPG, PNG, atau WEBP.');
                header('Location: ' . $redirect_url);
                exit;
            }
        }
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);
    $new_filename = time() . '_' . $safe_name;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        setFlashToast('danger', 'Gagal menyimpan file foto ke server.');
        header('Location: ' . $redirect_url);
        exit;
    }

    return $new_filename;
}

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_raw   = trim($_POST['harga'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');

    if ($nama_produk === '' || $harga_raw === '' || !is_numeric($harga_raw) || (float)$harga_raw < 0) {
        setFlashToast('danger', 'Nama produk dan harga wajib diisi dengan benar.');
        header('Location: ../pages/products/add.php');
        exit;
    }

    if (!in_array($kategori, $allowed_kategori, true)) {
        setFlashToast('danger', 'Kategori produk tidak valid. Pilih Minuman, Tambahan, atau Makanan.');
        header('Location: ../pages/products/add.php');
        exit;
    }

    $harga = (float)$harga_raw;
    $nama_file = uploadFotoProduk($_FILES['gambar'] ?? null, $upload_dir, '../pages/products/add.php');

    $stmt = mysqli_prepare($koneksi, "INSERT INTO produk (nama_produk, harga, kategori, gambar) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sdss', $nama_produk, $harga, $kategori, $nama_file);
        $hasil = mysqli_stmt_execute($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $hasil = false;
        $err_msg = mysqli_error($koneksi);
    }

    if ($hasil) {
        setFlashToast('success', 'Data produk berhasil ditambahkan.');
        header('Location: ../pages/products/list.php');
        exit;
    }

    if ($nama_file !== null && file_exists($upload_dir . $nama_file)) {
        @unlink($upload_dir . $nama_file);
    }
    setFlashToast('danger', 'Data produk gagal ditambahkan: ' . $err_msg);
    header('Location: ../pages/products/add.php');
    exit;
} else if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0);
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_raw   = trim($_POST['harga'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');
    $gambar_lama = trim($_POST['gambar_lama'] ?? '');

    $redirect_edit = '../pages/products/edit.php?id=' . urlencode((string)$id) . '&id_produk=' . urlencode((string)$id);

    if ($id <= 0 || $nama_produk === '' || $harga_raw === '' || !is_numeric($harga_raw) || (float)$harga_raw < 0) {
        setFlashToast('danger', 'Nama produk dan harga wajib diisi dengan benar.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    if (!in_array($kategori, $allowed_kategori, true)) {
        setFlashToast('danger', 'Kategori produk tidak valid. Pilih Minuman, Tambahan, atau Makanan.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    $harga = (float)$harga_raw;
    $nama_file = uploadFotoProduk($_FILES['gambar'] ?? null, $upload_dir, $redirect_edit);

    if ($nama_file !== null) {
        if ($gambar_lama !== '' && file_exists($upload_dir . basename($gambar_lama))) {
            @unlink($upload_dir . basename($gambar_lama));
        }
        $gambar_simpan = $nama_file;
    } else {
        $gambar_simpan = ($gambar_lama !== '') ? $gambar_lama : null;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE produk SET nama_produk = ?, harga = ?, kategori = ?, gambar = ? WHERE $col_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sdssi', $nama_produk, $harga, $kategori, $gambar_simpan, $id);
        $hasil = mysqli_stmt_execute($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $hasil = false;
        $err_msg = mysqli_error($koneksi);
    }

    if ($hasil) {
        setFlashToast('success', 'Data produk berhasil diperbarui.');
        header('Location: ../pages/products/list.php');
        exit;
    }

    setFlashToast('danger', 'Gagal mengupdate data produk: ' . $err_msg);
    header('Location: ' . $redirect_edit);
    exit;
} else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['id_produk']) ? (int)$_GET['id_produk'] : 0);

    if ($id <= 0) {
        setFlashToast('danger', 'ID produk tidak valid.');
        header('Location: ../pages/products/list.php');
        exit;
    }

    // Cek keterkaitan foreign key di detail_transaksi sebelum menghapus produk atau foto
    $stmt_cek = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jml FROM detail_transaksi WHERE id_produk = ?");
    if ($stmt_cek) {
        mysqli_stmt_bind_param($stmt_cek, 'i', $id);
        mysqli_stmt_execute($stmt_cek);
        $res_cek = mysqli_stmt_get_result($stmt_cek);
        $row_cek = $res_cek ? mysqli_fetch_assoc($res_cek) : null;
        mysqli_stmt_close($stmt_cek);
        if (($row_cek['jml'] ?? 0) > 0) {
            setFlashToast('danger', 'Produk ini sudah tercatat di riwayat transaksi dan tidak dapat dihapus.');
            header('Location: ../pages/products/list.php');
            exit;
        }
    }

    // Ambil nama file foto terlebih dahulu sebelum record dihapus
    $foto_lama = '';
    $stmt_foto = mysqli_prepare($koneksi, "SELECT gambar FROM produk WHERE $col_id = ? LIMIT 1");
    if ($stmt_foto) {
        mysqli_stmt_bind_param($stmt_foto, 'i', $id);
        mysqli_stmt_execute($stmt_foto);
        $res_foto = mysqli_stmt_get_result($stmt_foto);
        if ($res_foto && ($row_foto = mysqli_fetch_assoc($res_foto))) {
            $foto_lama = trim($row_foto['gambar'] ?? '');
        }
        mysqli_stmt_close($stmt_foto);
    }

    $stmt_del = mysqli_prepare($koneksi, "DELETE FROM produk WHERE $col_id = ?");
    if ($stmt_del) {
        mysqli_stmt_bind_param($stmt_del, 'i', $id);
        $hasil = mysqli_stmt_execute($stmt_del);
        $err_no = mysqli_stmt_errno($stmt_del);
        $err_msg = mysqli_stmt_error($stmt_del);
        mysqli_stmt_close($stmt_del);
    } else {
        $hasil = false;
        $err_no = mysqli_errno($koneksi);
        $err_msg = mysqli_error($koneksi);
    }

    if (!$hasil) {
        if ($err_no === 1451) {
            setFlashToast('danger', 'Produk ini sudah tercatat di riwayat transaksi dan tidak dapat dihapus.');
        } else {
            setFlashToast('danger', 'Gagal menghapus data produk: ' . $err_msg);
        }
        header('Location: ../pages/products/list.php');
        exit;
    }

    if ($foto_lama !== '' && file_exists($upload_dir . basename($foto_lama))) {
        @unlink($upload_dir . basename($foto_lama));
    }

    setFlashToast('success', 'Data produk berhasil dihapus.');
    header('Location: ../pages/products/list.php');
    exit;
}

header('Location: ../pages/products/list.php');
exit;
?>
