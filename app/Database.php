<?php

class Database
{
    private static ?mysqli $koneksi = null;

    private function __construct()
    {
    }

    /**
     * Ambil instance singleton koneksi MySQLi dengan zona waktu WITA (+08:00).
     */
    public static function ambilKoneksi(): mysqli
    {
        date_default_timezone_set('Asia/Makassar');

        if (self::$koneksi === null) {
            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = mysqli_connect('localhost', 'root', '', 'starbuck');
            if (!$conn) {
                die('Could not connect to database');
            }
            mysqli_set_charset($conn, 'utf8mb4');
            mysqli_query($conn, "SET time_zone = '+08:00'");
            self::$koneksi = $conn;
        }

        return self::$koneksi;
    }

    /**
     * Jalankan prepared statement dan kembalikan objek mysqli_stmt.
     */
    public static function siapkan(string $sql, string $types = '', array $params = [])
    {
        $conn = self::ambilKoneksi();
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }
        if ($types !== '' && !empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        return $stmt;
    }

    /**
     * Ambil seluruh baris hasil query sebagai array asosiatif.
     */
    public static function ambilSemua(string $sql, string $types = '', array $params = []): array
    {
        $conn = self::ambilKoneksi();
        if ($types === '' && empty($params)) {
            $res = mysqli_query($conn, $sql);
            if (!$res) {
                return [];
            }
            $rows = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $rows[] = $row;
            }
            return $rows;
        }

        $stmt = self::siapkan($sql, $types, $params);
        if (!$stmt) {
            return [];
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $rows = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $rows[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    /**
     * Ambil satu baris pertama hasil query.
     */
    public static function ambilSatu(string $sql, string $types = '', array $params = []): ?array
    {
        $rows = self::ambilSemua($sql, $types, $params);
        return $rows[0] ?? null;
    }
}
