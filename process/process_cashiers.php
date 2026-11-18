<?php
include '../config/config.php';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_kasir = trim($_POST['nama_kasir'] ?? '');
    $no_telp    = trim($_POST['no_telp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($nama_kasir === '' || $no_telp === '' || $alamat === '' || $email === '') {
        setFlashToast('danger', 'Nama kasir, no. telepon, alamat, dan email wajib diisi.');
        header('Location: ../pages/cashiers/add.php');
        exit;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        setFlashToast('danger', 'Format email tidak valid. Gunakan alamat email yang benar.');
        header('Location: ../pages/cashiers/add.php');
        exit;
    }

    // id_kasir dihasilkan otomatis oleh AUTO_INCREMENT MySQL
    $stmt = mysqli_prepare($koneksi, "INSERT INTO kasir (nama_kasir, no_telp, alamat, email) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssss', $nama_kasir, $no_telp, $alamat, $email);
        $hasil = mysqli_stmt_execute($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $hasil = false;
        $err_msg = mysqli_error($koneksi);
    }

    if ($hasil) {
        setFlashToast('success', 'Data kasir berhasil ditambahkan.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    setFlashToast('danger', 'Data gagal ditambahkan: ' . $err_msg);
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
        setFlashToast('danger', 'Semua data wajib diisi dengan lengkap.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        setFlashToast('danger', 'Format email tidak valid. Gunakan alamat email yang benar.');
        header('Location: ' . $redirect_edit);
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE kasir SET nama_kasir = ?, no_telp = ?, alamat = ?, email = ? WHERE id_kasir = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama_kasir, $no_telp, $alamat, $email, $id_kasir);
        $hasil = mysqli_stmt_execute($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $hasil = false;
        $err_msg = mysqli_error($koneksi);
    }

    if ($hasil) {
        setFlashToast('success', 'Data kasir berhasil diperbarui.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    setFlashToast('danger', 'Gagal mengupdate data kasir: ' . $err_msg);
    header('Location: ' . $redirect_edit);
    exit;
} else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_kasir = isset($_GET['id_kasir']) ? (int)$_GET['id_kasir'] : 0;

    if ($id_kasir <= 0) {
        setFlashToast('danger', 'ID kasir tidak valid.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM kasir WHERE id_kasir = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id_kasir);
        $hasil = mysqli_stmt_execute($stmt);
        $err_no = mysqli_stmt_errno($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $hasil = false;
        $err_no = mysqli_errno($koneksi);
        $err_msg = mysqli_error($koneksi);
    }

    if (!$hasil) {
        if ($err_no === 1451) {
            setFlashToast('danger', 'Kasir ini sudah tercatat pada riwayat transaksi dan tidak dapat dihapus.');
        } else {
            setFlashToast('danger', 'Gagal menghapus data kasir: ' . $err_msg);
        }
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    setFlashToast('success', 'Data kasir berhasil dihapus.');
    header('Location: ../pages/cashiers/list.php');
    exit;
}

header('Location: ../pages/cashiers/list.php');
exit;
?>
