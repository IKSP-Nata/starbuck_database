<?php

class HitungTransaksi
{
    public const PAJAK = 0.11;

    public static function hitung($harga, int $qty): array
    {
        $hargaSatuan    = max(0.0, (float)$harga);
        $kuantitas      = max(1, (int)$qty);
        $kuantitasHarga = (float)round($hargaSatuan * $kuantitas);
        $subtotal       = $kuantitasHarga;
        $pajak          = (float)round($subtotal * self::PAJAK);
        $total          = $subtotal + $pajak;

        return [
            'harga'           => $hargaSatuan,
            'kuantitas'       => $kuantitas,
            'kuantitas_harga' => $kuantitasHarga,
            'subtotal'        => $subtotal,
            'pajak'           => $pajak,
            'total'           => $total,
        ];
    }
}
