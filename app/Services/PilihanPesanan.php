<?php

class PilihanPesanan
{
    public const KATEGORI = ['Minuman', 'Tambahan', 'Makanan'];
    public const UKURAN   = ['Small', 'Medium', 'Large'];
    public const SUHU     = ['Ice', 'Hot'];
    public const GULA     = ['No sugar', 'Less sugar', 'Normal', 'High sugar'];

    public static function kategoriValid(?string $kategori): bool
    {
        return in_array(trim((string)$kategori), self::KATEGORI, true);
    }

    /**
     * Kustomisasi suhu/ukuran/gula hanya berlaku untuk Minuman; mendukung format 3 dan 2 bagian.
     */
    public static function susunCatatan(
        ?string $kategori,
        ?string $suhu = null,
        ?string $gula = null,
        ?string $catatanGabungan = null,
        ?string $ukuran = null
    ): ?string {
        $kat = trim((string)$kategori);
        if ($kat !== 'Minuman') {
            return null;
        }

        if (($suhu === null || $gula === null) && $catatanGabungan !== null && trim($catatanGabungan) !== '') {
            $parts = array_map('trim', explode(',', $catatanGabungan));
            if (count($parts) === 3) {
                $suhu   = $parts[0];
                $ukuran = $parts[1];
                $gula   = $parts[2];
            } elseif (count($parts) === 2) {
                $suhu = $parts[0];
                $gula = $parts[1];
            }
        }

        $suhuClean   = trim((string)$suhu);
        $ukuranClean = trim((string)$ukuran);
        $gulaClean   = trim((string)$gula);

        if (
            in_array($suhuClean, self::SUHU, true) &&
            in_array($ukuranClean, self::UKURAN, true) &&
            in_array($gulaClean, self::GULA, true)
        ) {
            return $suhuClean . ', ' . $ukuranClean . ', ' . $gulaClean;
        }

        if (
            in_array($suhuClean, self::SUHU, true) &&
            in_array($gulaClean, self::GULA, true)
        ) {
            return $suhuClean . ', ' . $gulaClean;
        }

        return null;
    }
}
