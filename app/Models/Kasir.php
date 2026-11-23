<?php

class Kasir
{
    public static function semua(string $orderBy = 'id'): array
    {
        $orderClause = $orderBy === 'nama' ? 'ORDER BY nama_kasir ASC' : 'ORDER BY id_kasir ASC';
        return Database::ambilSemua("SELECT id_kasir, nama_kasir, no_telp, alamat, email FROM kasir $orderClause");
    }

    public static function cari(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        return Database::ambilSatu(
            "SELECT id_kasir, nama_kasir, no_telp, alamat, email FROM kasir WHERE id_kasir = ? LIMIT 1",
            'i',
            [$id]
        );
    }

    public static function bisaDihapus(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $row = Database::ambilSatu("SELECT COUNT(*) AS jml FROM detail_transaksi WHERE id_kasir = ?", 'i', [$id]);
        return ((int)($row['jml'] ?? 0)) === 0;
    }

    public static function tambah(string $nama, string $noTelp, string $alamat, string $email): array
    {
        $conn = Database::ambilKoneksi();
        try {
            $stmt = Database::siapkan(
                "INSERT INTO kasir (nama_kasir, no_telp, alamat, email) VALUES (?, ?, ?, ?)",
                'ssss',
                [$nama, $noTelp, $alamat, $email]
            );
            if (!$stmt) {
                throw new Exception(mysqli_error($conn));
            }
            $ok = mysqli_stmt_execute($stmt);
            $err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);

            if (!$ok) {
                throw new Exception($err);
            }
            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function ubah(int $id, string $nama, string $noTelp, string $alamat, string $email): array
    {
        $conn = Database::ambilKoneksi();
        try {
            $stmt = Database::siapkan(
                "UPDATE kasir SET nama_kasir = ?, no_telp = ?, alamat = ?, email = ? WHERE id_kasir = ?",
                'ssssi',
                [$nama, $noTelp, $alamat, $email, $id]
            );
            if (!$stmt) {
                throw new Exception(mysqli_error($conn));
            }
            $ok = mysqli_stmt_execute($stmt);
            $err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);

            if (!$ok) {
                throw new Exception($err);
            }
            return ['ok' => true, 'error' => null];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function hapus(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'fk_blocked' => false, 'error' => 'ID kasir tidak valid.'];
        }

        if (!self::bisaDihapus($id)) {
            return [
                'ok'         => false,
                'fk_blocked' => true,
                'error'      => 'Kasir ini sudah tercatat pada data transaksi dan tidak dapat dihapus',
            ];
        }

        $conn = Database::ambilKoneksi();
        try {
            $stmt = Database::siapkan("DELETE FROM kasir WHERE id_kasir = ?", 'i', [$id]);
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
            return ['ok' => true, 'fk_blocked' => false, 'error' => null];
        } catch (Throwable $e) {
            $isFk = ((int)$e->getCode() === 1451 || stripos($e->getMessage(), 'foreign key') !== false);
            return [
                'ok'         => false,
                'fk_blocked' => $isFk,
                'error'      => $isFk
                    ? 'Kasir ini sudah tercatat pada data transaksi dan tidak dapat dihapus'
                    : $e->getMessage(),
            ];
        }
    }
}
