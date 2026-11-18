<?php

class Produk
{
    private static ?string $kolomIdCache = null;

    public static function kolomId(): string
    {
        if (self::$kolomIdCache !== null) {
            return self::$kolomIdCache;
        }
        $conn = Database::ambilKoneksi();
        $res = mysqli_query($conn, "SHOW COLUMNS FROM produk LIKE 'id_produk'");
        self::$kolomIdCache = ($res && mysqli_num_rows($res) > 0) ? 'id_produk' : 'id';
        return self::$kolomIdCache;
    }

    /**
     * Ambil seluruh daftar produk.
     */
    public static function semua(string $orderBy = ''): array
    {
        $colId = self::kolomId();
        $orderClause = $orderBy === 'nama' ? 'ORDER BY nama_produk ASC' : "ORDER BY $colId ASC";
        return Database::ambilSemua("SELECT * FROM produk $orderClause");
    }

    /**
     * Cari satu produk berdasarkan ID.
     */
    public static function cari(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $colId = self::kolomId();
        return Database::ambilSatu("SELECT * FROM produk WHERE $colId = ? LIMIT 1", 'i', [$id]);
    }

    /**
     * Cek apakah produk belum pernah dipakai di detail_transaksi.
     */
    public static function bisaDihapus(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $row = Database::ambilSatu("SELECT COUNT(*) AS jml FROM detail_transaksi WHERE id_produk = ?", 'i', [$id]);
        return ((int)($row['jml'] ?? 0)) === 0;
    }

    /**
     * Tambah produk baru ke database.
     */
    public static function tambah(string $nama, float $harga, string $kategori, ?string $gambar): array
    {
        $conn = Database::ambilKoneksi();
        $stmt = Database::siapkan(
            "INSERT INTO produk (nama_produk, harga, kategori, gambar) VALUES (?, ?, ?, ?)",
            'sdss',
            [$nama, $harga, $kategori, $gambar]
        );
        if (!$stmt) {
            return ['ok' => false, 'error' => mysqli_error($conn)];
        }
        $ok = mysqli_stmt_execute($stmt);
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        return ['ok' => (bool)$ok, 'error' => $ok ? null : $err];
    }

    /**
     * Ubah data produk berdasarkan ID.
     */
    public static function ubah(int $id, string $nama, float $harga, string $kategori, ?string $gambar): array
    {
        $conn = Database::ambilKoneksi();
        $colId = self::kolomId();
        $stmt = Database::siapkan(
            "UPDATE produk SET nama_produk = ?, harga = ?, kategori = ?, gambar = ? WHERE $colId = ?",
            'sdssi',
            [$nama, $harga, $kategori, $gambar, $id]
        );
        if (!$stmt) {
            return ['ok' => false, 'error' => mysqli_error($conn)];
        }
        $ok = mysqli_stmt_execute($stmt);
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        return ['ok' => (bool)$ok, 'error' => $ok ? null : $err];
    }

    /**
     * Hapus produk jika tidak terikat pada riwayat transaksi.
     */
    public static function hapus(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'fk_blocked' => false, 'gambar' => null, 'error' => 'ID produk tidak valid.'];
        }

        if (!self::bisaDihapus($id)) {
            return [
                'ok'         => false,
                'fk_blocked' => true,
                'gambar'     => null,
                'error'      => 'Produk ini sudah tercatat di riwayat transaksi dan tidak dapat dihapus',
            ];
        }

        $produk = self::cari($id);
        $gambarLama = trim((string)($produk['gambar'] ?? ''));
        $colId = self::kolomId();
        $conn = Database::ambilKoneksi();

        try {
            $stmt = Database::siapkan("DELETE FROM produk WHERE $colId = ?", 'i', [$id]);
            if (!$stmt) {
                throw new Exception(mysqli_error($conn), mysqli_errno($conn));
            }
            $ok = mysqli_stmt_execute($stmt);
            $errNo = mysqli_stmt_errno($stmt);
            $errMsg = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);

            if (!$ok) {
                throw new Exception($errMsg, $errNo);
            }

            return ['ok' => true, 'fk_blocked' => false, 'gambar' => $gambarLama, 'error' => null];
        } catch (Throwable $e) {
            $isFk = ((int)$e->getCode() === 1451 || stripos($e->getMessage(), 'foreign key') !== false);
            return [
                'ok'         => false,
                'fk_blocked' => $isFk,
                'gambar'     => null,
                'error'      => $isFk
                    ? 'Produk ini sudah tercatat di riwayat transaksi dan tidak dapat dihapus'
                    : $e->getMessage(),
            ];
        }
    }
}
