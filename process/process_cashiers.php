<?php
include '../config/config.php';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_kasir = trim($_POST['nama_kasir'] ?? '');
    $no_telp    = trim($_POST['no_telp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($nama_kasir === '' || $no_telp === '' || $alamat === '' || $email === '') {
        echo "<script>
        alert('Nama kasir, no. telepon, alamat, dan email wajib diisi.');
        window.location.href='../pages/cashiers/add.php';
        </script>";
        exit;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo "<script>
        alert('Format email tidak valid. Gunakan alamat email yang benar.');
        window.location.href='../pages/cashiers/add.php';
        </script>";
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
        echo "<script>
        alert('Data kasir berhasil ditambahkan.');
        window.location.href='../pages/cashiers/list.php';
        </script>";
    } else {
        echo "<script>
        alert('Data gagal ditambahkan: " . addslashes($err_msg) . "');
        window.location.href='../pages/cashiers/add.php';
        </script>";
    }
} else if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_kasir   = isset($_POST['id_kasir']) ? (int)$_POST['id_kasir'] : 0;
    $nama_kasir = trim($_POST['nama_kasir'] ?? '');
    $no_telp    = trim($_POST['no_telp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($id_kasir <= 0 || $nama_kasir === '' || $no_telp === '' || $alamat === '' || $email === '') {
        echo "<script>
        alert('Semua data wajib diisi dengan lengkap.');
        window.location.href='../pages/cashiers/edit.php?id_kasir=" . urlencode((string)$id_kasir) . "';
        </script>";
        exit;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo "<script>
        alert('Format email tidak valid. Gunakan alamat email yang benar.');
        window.location.href='../pages/cashiers/edit.php?id_kasir=" . urlencode((string)$id_kasir) . "';
        </script>";
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

    if (!$hasil) {
        echo "<script>
        alert('Gagal mengupdate data kasir: " . addslashes($err_msg) . "');
        window.location.href='../pages/cashiers/edit.php?id_kasir=" . urlencode((string)$id_kasir) . "';
        </script>";
    } else {
        echo "<script>
        alert('Data kasir berhasil diupdate.');
        window.location.href='../pages/cashiers/list.php';
        </script>";
    }
} else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_kasir = isset($_GET['id_kasir']) ? (int)$_GET['id_kasir'] : 0;

    if ($id_kasir <= 0) {
        echo "<script>
        alert('ID kasir tidak valid.');
        window.location.href='../pages/cashiers/list.php';
        </script>";
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
            $pesan = 'Kasir ini sudah tercatat pada riwayat transaksi dan tidak dapat dihapus.';
        } else {
            $pesan = 'Gagal menghapus data kasir: ' . $err_msg;
        }
        echo "<script>
        alert('" . addslashes($pesan) . "');
        window.location.href='../pages/cashiers/list.php';
        </script>";
    } else {
        echo "<script>
        alert('Data kasir berhasil dihapus.');
        window.location.href='../pages/cashiers/list.php';
        </script>";
    }
}
?>
