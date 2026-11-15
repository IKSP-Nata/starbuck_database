<?php
include '../config/config.php';

if (isset($_POST["action"]) && $_POST["action"] == "add") {
    $subtotal = $_POST['subtotal'] ?? '';
    $pajak    = $_POST['pajak'] ?? '0';
    $total    = $_POST['total'] ?? '';
    $tanggal  = $_POST['tanggal'] ?? '';
    $waktu    = $_POST['waktu'] ?? '';

    // Jika total kosong, hitung otomatis dari subtotal + pajak
    if ($total === '' || $total === null) {
        $total = (float)$subtotal + (float)$pajak;
    }

    // Validasi agar tidak bisa submit jika data utama masih kosong
    if ($subtotal === '' || empty(trim($tanggal)) || empty(trim($waktu))) {
        echo "<script>
        alert('Semua data wajib diisi! Mohon lengkapi formulir.');
        window.location.href='../pages/transactions/add.php';
        </script>";
        exit;
    }

    // Reset AUTO_INCREMENT agar ID mengikuti data terakhir yang ada
    $max_result = mysqli_query($koneksi, "SELECT IFNULL(MAX(id_transaksi), 0) + 1 AS next_id FROM transaksi");
    $max_row    = mysqli_fetch_assoc($max_result);
    $next_id    = (int)($max_row['next_id'] ?? 1);
    mysqli_query($koneksi, "ALTER TABLE transaksi AUTO_INCREMENT = $next_id");

    // Menambahkan data transaksi ke tabel transaksi
    $hasil = mysqli_query($koneksi, "INSERT INTO transaksi (subtotal, pajak, total, tanggal, waktu) VALUES ('$subtotal', '$pajak', '$total', '$tanggal', '$waktu')");

    if ($hasil) {
        echo "<script>
        alert('Data berhasil ditambahkan!');
        window.location.href='../pages/transactions/list.php';
        </script>";
    } else {
        echo "<script>
        alert('Data gagal ditambahkan: " . mysqli_error($koneksi) . "');
        window.location.href='../pages/transactions/add.php';
        </script>";
    }
} else if (isset($_POST["action"]) && $_POST["action"] == "edit") {
    $id_transaksi = $_POST["id_transaksi"] ?? '';
    $subtotal     = $_POST['subtotal'] ?? '';
    $pajak        = $_POST['pajak'] ?? '0';
    $total        = $_POST['total'] ?? '';
    $tanggal      = $_POST['tanggal'] ?? '';
    $waktu        = $_POST['waktu'] ?? '';

    if ($total === '' || $total === null) {
        $total = (float)$subtotal + (float)$pajak;
    }

    // Validasi data
    if (empty(trim($id_transaksi)) || $subtotal === '' || empty(trim($tanggal)) || empty(trim($waktu))) {
        echo "<script>
        alert('Semua data wajib diisi! Mohon lengkapi formulir.');
        window.location.href='../pages/transactions/edit.php?id_transaksi=$id_transaksi';
        </script>";
        exit;
    }

    // Mengupdate data transaksi di tabel transaksi
    $hasil = mysqli_query($koneksi, "UPDATE transaksi SET subtotal='$subtotal', pajak='$pajak', total='$total', tanggal='$tanggal', waktu='$waktu' WHERE id_transaksi='$id_transaksi'");

    if (!$hasil) {
        echo "<script>
        alert('Gagal mengupdate data transaksi: " . mysqli_error($koneksi) . "');
        window.location.href='../pages/transactions/edit.php?id_transaksi=$id_transaksi';
        </script>";
    } else {
        echo "<script>
        alert('Data transaksi berhasil diupdate.');
        window.location.href='../pages/transactions/list.php';
        </script>";
    }

} else if (isset($_GET["action"]) && $_GET["action"] == "delete") {
    $id_transaksi = $_GET["id_transaksi"] ?? '';

    // Menghapus data transaksi di tabel transaksi
    $hasil = mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi='$id_transaksi'");

    if (!$hasil) {
        echo "<script>
        alert('Gagal menghapus data transaksi: " . mysqli_error($koneksi) . "');
        window.location.href='../pages/transactions/list.php';
        </script>";
    } else {
        // Reset AUTO_INCREMENT agar ID berikutnya mengikuti data terakhir yang masih ada
        $max_result = mysqli_query($koneksi, "SELECT IFNULL(MAX(id_transaksi), 0) + 1 AS next_id FROM transaksi");
        $max_row    = mysqli_fetch_assoc($max_result);
        $next_id    = (int)($max_row['next_id'] ?? 1);
        mysqli_query($koneksi, "ALTER TABLE transaksi AUTO_INCREMENT = $next_id");

        echo "<script>
        alert('Data transaksi berhasil dihapus.');
        window.location.href='../pages/transactions/list.php';
        </script>";
    }
}
?>

