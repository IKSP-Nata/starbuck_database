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

    try {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kasir (nama_kasir, no_telp, alamat, email) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception(mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmt, 'ssss', $nama_kasir, $no_telp, $alamat, $email);
        $hasil = mysqli_stmt_execute($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        if (!$hasil) {
            throw new Exception($err_msg);
        }

        setFlashToast('success', 'Data kasir berhasil ditambahkan.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    } catch (Throwable $e) {
        setFlashToast('danger', 'Data gagal ditambahkan: ' . $e->getMessage());
        header('Location: ../pages/cashiers/add.php');
        exit;
    }
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

    try {
        $stmt = mysqli_prepare($koneksi, "UPDATE kasir SET nama_kasir = ?, no_telp = ?, alamat = ?, email = ? WHERE id_kasir = ?");
        if (!$stmt) {
            throw new Exception(mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama_kasir, $no_telp, $alamat, $email, $id_kasir);
        $hasil = mysqli_stmt_execute($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        if (!$hasil) {
            throw new Exception($err_msg);
        }

        setFlashToast('success', 'Data kasir berhasil diperbarui.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    } catch (Throwable $e) {
        setFlashToast('danger', 'Gagal mengupdate data kasir: ' . $e->getMessage());
        header('Location: ' . $redirect_edit);
        exit;
    }
} else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_kasir = isset($_GET['id_kasir']) ? (int)$_GET['id_kasir'] : 0;

    if ($id_kasir <= 0) {
        setFlashToast('danger', 'ID kasir tidak valid.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    }

    // 1. Cek keterkaitan kasir pada detail_transaksi terlebih dahulu
    $stmt_cek = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jml FROM detail_transaksi WHERE id_kasir = ?");
    if ($stmt_cek) {
        mysqli_stmt_bind_param($stmt_cek, 'i', $id_kasir);
        mysqli_stmt_execute($stmt_cek);
        $res_cek = mysqli_stmt_get_result($stmt_cek);
        $row_cek = $res_cek ? mysqli_fetch_assoc($res_cek) : null;
        mysqli_stmt_close($stmt_cek);

        if (($row_cek['jml'] ?? 0) > 0) {
            setFlashToast('danger', 'Kasir ini sudah tercatat pada data transaksi dan tidak dapat dihapus');
            header('Location: ../pages/cashiers/list.php');
            exit;
        }
    }

    // 2. Eksekusi DELETE di dalam blok try/catch untuk menangkap foreign key constraint
    try {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM kasir WHERE id_kasir = ?");
        if (!$stmt) {
            throw new Exception(mysqli_error($koneksi), mysqli_errno($koneksi));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_kasir);
        $hasil = mysqli_stmt_execute($stmt);
        $err_no = mysqli_stmt_errno($stmt);
        $err_msg = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        if (!$hasil) {
            throw new Exception($err_msg, $err_no);
        }

        setFlashToast('success', 'Data kasir berhasil dihapus.');
        header('Location: ../pages/cashiers/list.php');
        exit;
    } catch (Throwable $e) {
        if ((int)$e->getCode() === 1451 || stripos($e->getMessage(), 'foreign key') !== false) {
            setFlashToast('danger', 'Kasir ini sudah tercatat pada data transaksi dan tidak dapat dihapus');
        } else {
            setFlashToast('danger', 'Gagal menghapus data kasir: ' . $e->getMessage());
        }
        header('Location: ../pages/cashiers/list.php');
        exit;
    }
}

header('Location: ../pages/cashiers/list.php');
exit;
?>
