<?php
date_default_timezone_set('Asia/Makassar');

require_once __DIR__ . '/../app/autoload.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Koneksi singleton MySQLi (tetap disediakan pada variabel $koneksi untuk kompatibilitas)
$koneksi = Database::ambilKoneksi();

if (!function_exists('setFlashToast')) {
    function setFlashToast($type, $message)
    {
        Flash::set((string)$type, (string)$message);
    }
}

if (!function_exists('getFlashToast')) {
    function getFlashToast()
    {
        return Flash::get();
    }
}

if (!function_exists('formatTanggalIndo')) {
    function formatTanggalIndo($ymd)
    {
        return Format::tanggalIndo((string)$ymd);
    }
}

if (!function_exists('formatBulanIndo')) {
    function formatBulanIndo($ym)
    {
        return Format::bulanIndo((string)$ym);
    }
}

if (!function_exists('getTransaksiFilter')) {
    function getTransaksiFilter($koneksi, $params)
    {
        return Transaksi::buatFilter(is_array($params) ? $params : []);
    }
}
