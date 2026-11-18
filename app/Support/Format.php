<?php

class Format
{
    /**
     * Format angka menjadi "Rp 45.000".
     */
    public static function rupiah($nominal): string
    {
        return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
    }

    /**
     * Format angka menjadi nominal ringkas untuk grafik ("Rp 125rb", "Rp 1,2jt", "Rp 0").
     */
    public static function rupiahRingkas($nominal): string
    {
        $angka = (float)$nominal;
        if ($angka <= 0) {
            return 'Rp 0';
        }
        if ($angka >= 1000000) {
            $jt = round($angka / 1000000, 1);
            return 'Rp ' . str_replace('.', ',', (string)$jt) . 'jt';
        }
        if ($angka >= 1000) {
            return 'Rp ' . number_format(round($angka / 1000), 0, ',', '.') . 'rb';
        }
        return self::rupiah($angka);
    }

    /**
     * Format tanggal YYYY-MM-DD menjadi "18 Nov 2026".
     */
    public static function tanggalIndo(?string $ymd): string
    {
        $ymd = trim((string)$ymd);
        if ($ymd === '') {
            return '-';
        }
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

    /**
     * Alias untuk tanggalIndo().
     */
    public static function tanggal(?string $ymd): string
    {
        return self::tanggalIndo($ymd);
    }

    /**
     * Format bulan YYYY-MM menjadi "November 2026".
     */
    public static function bulanIndo(?string $ym): string
    {
        $ym = trim((string)$ym);
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

    /**
     * Format waktu WITA.
     */
    public static function waktuWita(?string $waktu): string
    {
        return trim((string)$waktu);
    }

    /**
     * Alias untuk waktuWita().
     */
    public static function waktu(?string $waktu): string
    {
        return self::waktuWita($waktu);
    }
}
