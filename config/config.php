<?php
date_default_timezone_set('Asia/Makassar');

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$koneksi = mysqli_connect("localhost", "root", "", "starbuck") or die("Could not connect to database");
mysqli_query($koneksi, "SET time_zone = '+08:00'");

if (!function_exists('setFlashToast')) {
    /**
     * Simpan pesan notifikasi Toast ke session untuk ditampilkan setelah redirect.
     */
    function setFlashToast($type, $message)
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION['sb_flash_toast'] = [
            'type'    => (string)$type,
            'message' => (string)$message,
        ];
    }
}

if (!function_exists('getFlashToast')) {
    /**
     * Ambil lalu hapus pesan notifikasi Toast dari session.
     */
    function getFlashToast()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (!empty($_SESSION['sb_flash_toast']) && is_array($_SESSION['sb_flash_toast'])) {
            $flash = $_SESSION['sb_flash_toast'];
            unset($_SESSION['sb_flash_toast']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('formatTanggalIndo')) {
    /**
     * Format tanggal YYYY-MM-DD menjadi "01 Nov 2026".
     */
    function formatTanggalIndo($ymd)
    {
        $bulan_indo = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];
        $parts = explode('-', $ymd);
        if (count($parts) === 3 && isset($bulan_indo[$parts[1]])) {
            return $parts[2] . ' ' . $bulan_indo[$parts[1]] . ' ' . $parts[0];
        }
        return $ymd;
    }
}

if (!function_exists('formatBulanIndo')) {
    /**
     * Format bulan YYYY-MM menjadi "November 2026".
     */
    function formatBulanIndo($ym)
    {
        $bulan_panjang = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $parts = explode('-', $ym);
        if (count($parts) === 2 && isset($bulan_panjang[$parts[1]])) {
            return $bulan_panjang[$parts[1]] . ' ' . $parts[0];
        }
        return $ym;
    }
}

if (!function_exists('getTransaksiFilter')) {
    /**
     * Bangun klausa WHERE dan keterangan periode transaksi berdasarkan parameter GET.
     * Prioritas: rentang tanggal -> bulan -> hari_ini -> semua -> default bulan berjalan.
     */
    function getTransaksiFilter($koneksi, $params)
    {
        $tanggal_awal  = trim($params['tanggal_awal'] ?? '');
        $tanggal_akhir = trim($params['tanggal_akhir'] ?? '');
        $bulan         = trim($params['bulan'] ?? '');
        $mode          = trim($params['mode'] ?? '');

        $valid_awal  = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) === 1;
        $valid_akhir = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir) === 1;
        $valid_bulan = preg_match('/^\d{4}-\d{2}$/', $bulan) === 1;

        if ($valid_awal && $valid_akhir) {
            if ($tanggal_awal > $tanggal_akhir) {
                $tmp = $tanggal_awal;
                $tanggal_awal = $tanggal_akhir;
                $tanggal_akhir = $tmp;
            }
            $awal_esc  = mysqli_real_escape_string($koneksi, $tanggal_awal);
            $akhir_esc = mysqli_real_escape_string($koneksi, $tanggal_akhir);
            return [
                'where_sql'     => "WHERE t.tanggal BETWEEN '$awal_esc' AND '$akhir_esc'",
                'periode_label' => formatTanggalIndo($tanggal_awal) . ' - ' . formatTanggalIndo($tanggal_akhir),
                'active_mode'   => 'rentang',
                'tanggal_awal'  => $tanggal_awal,
                'tanggal_akhir' => $tanggal_akhir,
                'bulan'         => '',
            ];
        }

        if ($valid_bulan) {
            $bulan_esc = mysqli_real_escape_string($koneksi, $bulan);
            return [
                'where_sql'     => "WHERE DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulan_esc'",
                'periode_label' => formatBulanIndo($bulan),
                'active_mode'   => 'bulan',
                'tanggal_awal'  => '',
                'tanggal_akhir' => '',
                'bulan'         => $bulan,
            ];
        }

        if ($mode === 'hari_ini') {
            $hari_ini     = date('Y-m-d');
            $hari_ini_esc = mysqli_real_escape_string($koneksi, $hari_ini);
            return [
                'where_sql'     => "WHERE t.tanggal = '$hari_ini_esc'",
                'periode_label' => 'Hari Ini (' . formatTanggalIndo($hari_ini) . ')',
                'active_mode'   => 'hari_ini',
                'tanggal_awal'  => '',
                'tanggal_akhir' => '',
                'bulan'         => '',
            ];
        }

        if ($mode === 'semua') {
            return [
                'where_sql'     => '',
                'periode_label' => 'Semua Periode',
                'active_mode'   => 'semua',
                'tanggal_awal'  => '',
                'tanggal_akhir' => '',
                'bulan'         => '',
            ];
        }

        $bulan_ini     = date('Y-m');
        $bulan_ini_esc = mysqli_real_escape_string($koneksi, $bulan_ini);
        return [
            'where_sql'     => "WHERE DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulan_ini_esc'",
            'periode_label' => formatBulanIndo($bulan_ini),
            'active_mode'   => 'bulan_ini',
            'tanggal_awal'  => '',
            'tanggal_akhir' => '',
            'bulan'         => '',
        ];
    }
}


