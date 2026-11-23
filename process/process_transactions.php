<?php
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'add')) {
    $id_kasir = isset($_POST['id_kasir']) ? (int)$_POST['id_kasir'] : 0;
    $items    = (isset($_POST['items']) && is_array($_POST['items'])) ? $_POST['items'] : [];

    // Kompatibilitas payload form 1-item lama
    if (empty($items) && !empty($_POST['id_produk'])) {
        $items = [[
            'id_produk' => (int)$_POST['id_produk'],
            'kuantitas' => isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1,
            'catatan'   => trim((string)($_POST['catatan'] ?? '')),
        ]];
    }

    $res = Transaksi::buatMulti($id_kasir, $items);
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
