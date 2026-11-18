<?php

class PilihanPesanan
{
    public const KATEGORI = ['Minuman', 'Tambahan', 'Makanan'];
    public const SUHU     = ['Ice', 'Hot'];
    public const GULA     = ['No sugar', 'Less sugar', 'Normal', 'High sugar'];

    public static function kategoriValid(?string $kategori): bool
    {
        return in_array(trim((string)$kategori), self::KATEGORI, true);
    }

    /**
     * Susun dan validasi catatan kustomisasi suhu & gula khusus kategori Minuman.
     * Untuk kategori Tambahan atau Makanan, selalu mengembalikan null.
     */
    public static function susunCatatan(
        ?string $kategori,
        ?string $suhu = null,
        ?string $gula = null,
        ?string $catatanGabungan = null
    ): ?string {
        $kat = trim((string)$kategori);
        if ($kat !== 'Minuman') {
            return null;
        }

        if (($suhu === null || $gula === null) && $catatanGabungan !== null && trim($catatanGabungan) !== '') {
            $parts = array_map('trim', explode(',', $catatanGabungan));
            if (count($parts) === 2) {
                $suhu = $parts[0];
                $gula = $parts[1];
            }
        }

        $suhuClean = trim((string)$suhu);
        $gulaClean = trim((string)$gula);

        if (
            in_array($suhuClean, self::SUHU, true) &&
            in_array($gulaClean, self::GULA, true)
        ) {
            return $suhuClean . ', ' . $gulaClean;
        }

        return null;
    }
}
