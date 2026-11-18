<?php
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'add')) {
    $id_produk   = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $id_kasir    = isset($_POST['id_kasir']) ? (int)$_POST['id_kasir'] : 0;
    $kuantitas   = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
    $catatan_raw = trim($_POST['catatan'] ?? '');

    $res = Transaksi::buat($id_produk, $id_kasir, $kuantitas, $catatan_raw);
    if ($res['ok']) {
        Flash::set('success', 'Transaksi berhasil disimpan.');
        header('Location: ../pages/transactions/list.php');
        exit;
    }

    Flash::set('danger', 'Gagal menyimpan transaksi: ' . ($res['error'] ?? ''));
    header('Location: ../pages/transactions/add.php');
    exit;
}

header('Location: ../pages/transactions/list.php');
exit;
?>
