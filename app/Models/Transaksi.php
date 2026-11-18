<?php

class Transaksi
{
    /**
     * Buat klausa filter periode transaksi (WITA) dari parameter GET.
     */
    public static function buatFilter(array $params): array
    {
        $conn = Database::ambilKoneksi();
        $tanggalAwal  = trim($params['tanggal_awal'] ?? '');
        $tanggalAkhir = trim($params['tanggal_akhir'] ?? '');
        $bulan        = trim($params['bulan'] ?? '');
        $mode         = trim($params['mode'] ?? '');

        $validAwal  = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAwal) === 1;
        $validAkhir = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAkhir) === 1;
        $validBulan = preg_match('/^\d{4}-\d{2}$/', $bulan) === 1;

        if ($validAwal && $validAkhir) {
            if ($tanggalAwal > $tanggalAkhir) {
                $tmp = $tanggalAwal;
                $tanggalAwal = $tanggalAkhir;
                $tanggalAkhir = $tmp;
            }
            $awalEsc  = mysqli_real_escape_string($conn, $tanggalAwal);
            $akhirEsc = mysqli_real_escape_string($conn, $tanggalAkhir);
            return [
                'where_sql'     => "WHERE t.tanggal BETWEEN '$awalEsc' AND '$akhirEsc'",
                'periode_label' => Format::tanggalIndo($tanggalAwal) . ' - ' . Format::tanggalIndo($tanggalAkhir),
                'active_mode'   => 'rentang',
                'tanggal_awal'  => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
                'bulan'         => '',
            ];
        }

        if ($validBulan) {
            $bulanEsc = mysqli_real_escape_string($conn, $bulan);
            return [
                'where_sql'     => "WHERE DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulanEsc'",
                'periode_label' => Format::bulanIndo($bulan),
                'active_mode'   => 'bulan',
                'tanggal_awal'  => '',
                'tanggal_akhir' => '',
                'bulan'         => $bulan,
            ];
        }

        if ($mode === 'hari_ini') {
            $hariIni    = date('Y-m-d');
            $hariIniEsc = mysqli_real_escape_string($conn, $hariIni);
            return [
                'where_sql'     => "WHERE t.tanggal = '$hariIniEsc'",
                'periode_label' => 'Hari Ini (' . Format::tanggalIndo($hariIni) . ')',
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

        $bulanIni    = date('Y-m');
        $bulanIniEsc = mysqli_real_escape_string($conn, $bulanIni);
        return [
            'where_sql'     => "WHERE DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulanIniEsc'",
            'periode_label' => Format::bulanIndo($bulanIni),
            'active_mode'   => 'bulan_ini',
            'tanggal_awal'  => '',
            'tanggal_akhir' => '',
            'bulan'         => '',
        ];
    }

    /**
     * Lengkapi array transaksi dengan rincian item, nama kasir, dan catatan dari detail_transaksi.
     */
    private static function lengkapiDetail(array $transaksiMap): array
    {
        if (empty($transaksiMap)) {
            return [];
        }
        $idsSql = implode(',', array_map('intval', array_keys($transaksiMap)));
        $detailRows = Database::ambilSemua(
            "SELECT dt.id_transaksi, dt.kuantitas, dt.catatan, p.nama_produk, k.nama_kasir
             FROM detail_transaksi dt
             LEFT JOIN produk p ON dt.id_produk = p.id_produk
             LEFT JOIN kasir k ON dt.id_kasir = k.id_kasir
             WHERE dt.id_transaksi IN ($idsSql)
             ORDER BY dt.id_detail_transaksi ASC"
        );

        foreach ($detailRows as $det) {
            $idTrx = (int)$det['id_transaksi'];
            if (!isset($transaksiMap[$idTrx])) {
                continue;
            }
            if ($transaksiMap[$idTrx]['nama_kasir'] === '-' && !empty($det['nama_kasir'])) {
                $transaksiMap[$idTrx]['nama_kasir'] = $det['nama_kasir'];
            }
            if (!empty($det['nama_produk'])) {
                $qtyItem = ($det['kuantitas'] !== null) ? (int)$det['kuantitas'] : 0;
                $transaksiMap[$idTrx]['items'][] = [
                    'nama_produk' => $det['nama_produk'],
                    'kuantitas'   => $qtyItem,
                    'catatan'     => trim((string)($det['catatan'] ?? '')),
                ];
                if ($transaksiMap[$idTrx]['total_kuantitas'] === null) {
                    $transaksiMap[$idTrx]['total_kuantitas'] = 0;
                }
                $transaksiMap[$idTrx]['total_kuantitas'] += $qtyItem;
            }
        }

        return $transaksiMap;
    }

    /**
     * Ambil daftar riwayat transaksi sesuai filter periode beserta total pendapatan.
     */
    public static function ambilRiwayat(array $filter): array
    {
        $whereSql = $filter['where_sql'] ?? '';
        $rows = Database::ambilSemua(
            "SELECT t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu
             FROM transaksi t
             $whereSql
             ORDER BY t.id_transaksi DESC"
        );

        $map = [];
        $totalPendapatan = 0.0;
        foreach ($rows as $row) {
            $idTrx = (int)$row['id_transaksi'];
            $row['nama_kasir'] = '-';
            $row['items'] = [];
            $row['total_kuantitas'] = null;
            $map[$idTrx] = $row;
            $totalPendapatan += (float)$row['total'];
        }

        return [
            'rows'             => self::lengkapiDetail($map),
            'total_pendapatan' => $totalPendapatan,
        ];
    }

    /**
     * Ambil N transaksi terbaru untuk ditampilkan di Dashboard.
     */
    public static function terbaru(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $rows = Database::ambilSemua(
            "SELECT id_transaksi, subtotal, pajak, total, tanggal, waktu
             FROM transaksi
             ORDER BY id_transaksi DESC
             LIMIT $limit"
        );

        $map = [];
        foreach ($rows as $row) {
            $idTrx = (int)$row['id_transaksi'];
            $row['nama_kasir'] = '-';
            $row['items'] = [];
            $row['total_kuantitas'] = null;
            $map[$idTrx] = $row;
        }

        return self::lengkapiDetail($map);
    }

    /**
     * Cari satu transaksi beserta daftar item lengkap untuk cetak struk 80mm.
     */
    public static function cariDetail(int $idTransaksi): ?array
    {
        if ($idTransaksi <= 0) {
            return null;
        }

        $rows = Database::ambilSemua(
            "SELECT t.id_transaksi, t.subtotal, t.pajak, t.total, t.tanggal, t.waktu,
                    COALESCE(k.nama_kasir, '-') AS nama_kasir,
                    dt.kuantitas, dt.catatan,
                    CASE
                        WHEN dt.harga IS NOT NULL AND dt.harga > 0 THEN dt.harga
                        ELSE p.harga
                    END AS harga_satuan,
                    p.nama_produk
             FROM transaksi t
             LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
             LEFT JOIN kasir k ON dt.id_kasir = k.id_kasir
             LEFT JOIN produk p ON dt.id_produk = p.id_produk
             WHERE t.id_transaksi = ?
             ORDER BY dt.id_detail_transaksi ASC",
            'i',
            [$idTransaksi]
        );

        if (empty($rows)) {
            return null;
        }

        $header = null;
        $items = [];
        foreach ($rows as $row) {
            if ($header === null) {
                $header = [
                    'id_transaksi' => $row['id_transaksi'],
                    'nama_kasir'   => $row['nama_kasir'],
                    'subtotal'     => (float)$row['subtotal'],
                    'pajak'        => (float)$row['pajak'],
                    'total'        => (float)$row['total'],
                    'tanggal'      => $row['tanggal'],
                    'waktu'        => $row['waktu'],
                ];
            } elseif ($header['nama_kasir'] === '-' && $row['nama_kasir'] !== '-') {
                $header['nama_kasir'] = $row['nama_kasir'];
            }

            if (!empty($row['nama_produk'])) {
                $items[] = [
                    'nama_produk'  => $row['nama_produk'],
                    'harga_satuan' => (float)$row['harga_satuan'],
                    'kuantitas'    => (int)$row['kuantitas'],
                    'catatan'      => trim((string)($row['catatan'] ?? '')),
                ];
            }
        }

        $header['items'] = $items;
        return $header;
    }

    /**
     * Ambil ringkasan 4 metrik statistik utama Dashboard.
     */
    public static function statistik(): array
    {
        $kasirRow  = Database::ambilSatu("SELECT COUNT(*) AS total FROM kasir");
        $produkRow = Database::ambilSatu("SELECT COUNT(*) AS total FROM produk");
        $trxRow    = Database::ambilSatu("SELECT COUNT(*) AS total_trx, COALESCE(SUM(total), 0) AS total_rev FROM transaksi");

        return [
            'total_kasir'      => (int)($kasirRow['total'] ?? 0),
            'total_produk'     => (int)($produkRow['total'] ?? 0),
            'total_transaksi'  => (int)($trxRow['total_trx'] ?? 0),
            'total_pendapatan' => (float)($trxRow['total_rev'] ?? 0),
        ];
    }

    /**
     * Ambil data grafik pendapatan 7 hari terakhir (WITA).
     */
    public static function pendapatan7Hari(array $hariSingkat): array
    {
        $todayYmd = date('Y-m-d');
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $ts  = strtotime("-$i days");
            $ymd = date('Y-m-d', $ts);
            $parts = explode(' ', Format::tanggalIndo($ymd));
            $tglPendek = isset($parts[0], $parts[1]) ? ($parts[0] . ' ' . $parts[1]) : $ymd;

            $days[$ymd] = [
                'tanggal'    => $ymd,
                'hari'       => $hariSingkat[date('l', $ts)] ?? date('D', $ts),
                'tgl_pendek' => $tglPendek,
                'total'      => 0.0,
                'is_today'   => ($ymd === $todayYmd),
            ];
        }

        $start7d = date('Y-m-d', strtotime('-6 days'));
        $rows = Database::ambilSemua(
            "SELECT tanggal, COALESCE(SUM(total), 0) AS total_hari
             FROM transaksi
             WHERE tanggal BETWEEN ? AND ?
             GROUP BY tanggal",
            'ss',
            [$start7d, $todayYmd]
        );

        $maxHarian = 0.0;
        foreach ($rows as $r) {
            $tglKey = $r['tanggal'];
            $nilai  = (float)$r['total_hari'];
            if (isset($days[$tglKey])) {
                $days[$tglKey]['total'] = $nilai;
                if ($nilai > $maxHarian) {
                    $maxHarian = $nilai;
                }
            }
        }

        return [
            'days'       => $days,
            'max_harian' => $maxHarian,
        ];
    }

    /**
     * Ambil Top N menu terlaris berdasarkan SUM(kuantitas).
     */
    public static function menuTerlaris(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $rows = Database::ambilSemua(
            "SELECT p.id_produk, p.nama_produk, p.gambar, COALESCE(SUM(dt.kuantitas), 0) AS total_terjual
             FROM detail_transaksi dt
             INNER JOIN produk p ON dt.id_produk = p.id_produk
             GROUP BY p.id_produk, p.nama_produk, p.gambar
             HAVING total_terjual > 0
             ORDER BY total_terjual DESC, p.nama_produk ASC
             LIMIT $limit"
        );

        $maxTerjual = 0;
        foreach ($rows as $r) {
            $terjual = (int)$r['total_terjual'];
            if ($terjual > $maxTerjual) {
                $maxTerjual = $terjual;
            }
        }

        return [
            'items'       => $rows,
            'max_terjual' => $maxTerjual,
        ];
    }

    /**
     * Simpan transaksi baru ke tabel transaksi & detail_transaksi menggunakan database transaction.
     */
    public static function buat(int $idProduk, int $idKasir, int $kuantitas, ?string $catatanRaw): array
    {
        if ($idProduk <= 0 || $idKasir <= 0 || $kuantitas < 1) {
            return ['ok' => false, 'error' => 'Pilih menu, kasir bertugas, dan jumlah beli yang valid.'];
        }

        $kasir = Kasir::cari($idKasir);
        if (!$kasir) {
            return ['ok' => false, 'error' => 'Data kasir bertugas tidak ditemukan.'];
        }

        $produk = Produk::cari($idProduk);
        if (!$produk) {
            return ['ok' => false, 'error' => 'Data produk tidak ditemukan.'];
        }

        $harga    = (float)($produk['harga'] ?? 0);
        $kategori = trim((string)($produk['kategori'] ?? 'Minuman'));
        if (!PilihanPesanan::kategoriValid($kategori)) {
            $kategori = 'Minuman';
        }

        $catatan  = PilihanPesanan::susunCatatan($kategori, null, null, $catatanRaw);
        $hitungan = HitungTransaksi::hitung($harga, $kuantitas);

        $subtotal       = $hitungan['subtotal'];
        $pajak          = $hitungan['pajak'];
        $total          = $hitungan['total'];
        $kuantitasHarga = $hitungan['kuantitas_harga'];
        $tanggal        = date('Y-m-d');
        $waktu          = date('H:i:s');

        $conn = Database::ambilKoneksi();
        mysqli_begin_transaction($conn);

        try {
            $maxTrxRow   = Database::ambilSatu("SELECT IFNULL(MAX(id_transaksi), 0) + 1 AS next_id FROM transaksi");
            $idTransaksi = (int)($maxTrxRow['next_id'] ?? 1);

            $stmtTrx = Database::siapkan(
                "INSERT INTO transaksi (id_transaksi, subtotal, pajak, total, tanggal, waktu) VALUES (?, ?, ?, ?, ?, ?)",
                'idddss',
                [$idTransaksi, $subtotal, $pajak, $total, $tanggal, $waktu]
            );
            if (!$stmtTrx) {
                throw new Exception(mysqli_error($conn));
            }
            if (!mysqli_stmt_execute($stmtTrx)) {
                $err = mysqli_stmt_error($stmtTrx);
                mysqli_stmt_close($stmtTrx);
                throw new Exception($err);
            }
            mysqli_stmt_close($stmtTrx);

            $maxDetRow = Database::ambilSatu("SELECT IFNULL(MAX(id_detail_transaksi), 0) + 1 AS next_id FROM detail_transaksi");
            $idDetail  = (int)($maxDetRow['next_id'] ?? 1);

            $stmtDet = Database::siapkan(
                "INSERT INTO detail_transaksi (id_detail_transaksi, id_kasir, id_produk, id_transaksi, kategori, kuantitas, kuantitas_harga, catatan, harga)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                'iiiisidsd',
                [$idDetail, $idKasir, $idProduk, $idTransaksi, $kategori, $kuantitas, $kuantitasHarga, $catatan, $harga]
            );
            if (!$stmtDet) {
                throw new Exception(mysqli_error($conn));
            }
            if (!mysqli_stmt_execute($stmtDet)) {
                $err = mysqli_stmt_error($stmtDet);
                mysqli_stmt_close($stmtDet);
                throw new Exception($err);
            }
            mysqli_stmt_close($stmtDet);

            mysqli_commit($conn);
            return ['ok' => true, 'id_transaksi' => $idTransaksi, 'error' => null];
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
