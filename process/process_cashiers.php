<?php
include '../config/config.php';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_kasir = trim($_POST['nama_kasir'] ?? '');
    $no_telp    = trim($_POST['no_telp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($nama_kasir === '' || $no_telp === '' || $alamat === '' || $email === '') {
        Flash::set('danger', 'Nama kasir, no. telepon, alamat, dan email wajib diisi.');
        header('Location: ../pages/cashiers/add.php');
        exit;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        Flash::set('danger', 'Format email tidak valid. Gunakan alamat email yang benar.');
        header('Location: ../pages/cashiers/add.php');
        exit;
    }

    $res = Kasir::tambah($nama_kasir, $no_telp, $alamat, $email);
    if ($res['ok']) {
        Flash::set('success', 'Data kasir berhasil ditambahkan.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    Flash::set('danger', 'Data gagal ditambahkan: ' . ($res['error'] ?? ''));
    header('Location: ../pages/cashiers/add.php');
    exit;
} else if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_kasir   = isset($_POST['id_kasir']) ? (int)$_POST['id_kasir'] : 0;
    $nama_kasir = trim($_POST['nama_kasir'] ?? '');
    $no_telp    = trim($_POST['no_telp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    $redirect_edit = '../pages/cashiers/edit.php?id_kasir=' . urlencode((string)$id_kasir);

    if ($id_kasir <= 0 || $nama_kasir === '' || $no_telp === '' || $alamat === '' || $email === '') {
        Flash::set('danger', 'Semua data wajib diisi dengan lengkap.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        Flash::set('danger', 'Format email tidak valid. Gunakan alamat email yang benar.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    $res = Kasir::ubah($id_kasir, $nama_kasir, $no_telp, $alamat, $email);
    if ($res['ok']) {
        Flash::set('success', 'Data kasir berhasil diperbarui.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    Flash::set('danger', 'Gagal mengupdate data kasir: ' . ($res['error'] ?? ''));
    header('Location: ' . $redirect_edit);
    exit;
} else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_kasir = isset($_GET['id_kasir']) ? (int)$_GET['id_kasir'] : 0;

    $res = Kasir::hapus($id_kasir);
    if (!$res['ok']) {
        Flash::set('danger', $res['error'] ?? 'Gagal menghapus data kasir.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    Flash::set('success', 'Data kasir berhasil dihapus.');
    header('Location: ../pages/cashiers/list.php');
    exit;
}

header('Location: ../pages/cashiers/list.php');
exit;
?>
