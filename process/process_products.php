<?php
include '../config/config.php';

$upload_dir = '../assets/img/products/';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_raw   = trim($_POST['harga'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');

    if ($nama_produk === '' || $harga_raw === '' || !is_numeric($harga_raw) || (float)$harga_raw < 0) {
        Flash::set('danger', 'Nama produk dan harga wajib diisi dengan benar.');
        header('Location: ../pages/products/add.php');
        exit;
    }

    if (!PilihanPesanan::kategoriValid($kategori)) {
        Flash::set('danger', 'Kategori produk tidak valid. Pilih Minuman, Tambahan, atau Makanan.');
        header('Location: ../pages/products/add.php');
        exit;
    }

    $upload = Upload::unggahGambar($_FILES['gambar'] ?? null, $upload_dir);
    if (!$upload['ok']) {
        Flash::set('danger', $upload['error'] ?? 'Gagal mengunggah foto produk.');
        header('Location: ../pages/products/add.php');
        exit;
    }

    $nama_file = $upload['filename'];
    $res = Produk::tambah($nama_produk, (float)$harga_raw, $kategori, $nama_file);

    if ($res['ok']) {
        Flash::set('success', 'Data produk berhasil ditambahkan.');
        header('Location: ../pages/products/list.php');
        exit;
    }

    Upload::hapusGambar($nama_file, $upload_dir);
    Flash::set('danger', 'Data produk gagal ditambahkan: ' . ($res['error'] ?? ''));
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
        Flash::set('danger', 'Nama produk dan harga wajib diisi dengan benar.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    if (!PilihanPesanan::kategoriValid($kategori)) {
        Flash::set('danger', 'Kategori produk tidak valid. Pilih Minuman, Tambahan, atau Makanan.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    $upload = Upload::unggahGambar($_FILES['gambar'] ?? null, $upload_dir);
    if (!$upload['ok']) {
        Flash::set('danger', $upload['error'] ?? 'Gagal mengunggah foto produk.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    if ($upload['filename'] !== null) {
        Upload::hapusGambar($gambar_lama, $upload_dir);
        $gambar_simpan = $upload['filename'];
    } else {
        $gambar_simpan = ($gambar_lama !== '') ? $gambar_lama : null;
    }

    $res = Produk::ubah($id, $nama_produk, (float)$harga_raw, $kategori, $gambar_simpan);

    if ($res['ok']) {
        Flash::set('success', 'Data produk berhasil diperbarui.');
        header('Location: ../pages/products/list.php');
        exit;
    }

    Flash::set('danger', 'Gagal mengupdate data produk: ' . ($res['error'] ?? ''));
    header('Location: ' . $redirect_edit);
    exit;
} else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['id_produk']) ? (int)$_GET['id_produk'] : 0);

    $res = Produk::hapus($id);
    if (!$res['ok']) {
        Flash::set('danger', $res['error'] ?? 'Gagal menghapus data produk.');
        header('Location: ../pages/products/list.php');
        exit;
    }

    Upload::hapusGambar($res['gambar'] ?? null, $upload_dir);
    Flash::set('success', 'Data produk berhasil dihapus.');
    header('Location: ../pages/products/list.php');
    exit;
}

header('Location: ../pages/products/list.php');
exit;
?>
