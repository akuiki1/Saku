<?php

namespace App\Services;

use App\Models\Kwitansi;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfKwitansi
{
    /**
     * Ukuran kertas F4 / Folio (mm). Bisa diganti ke Legal [215.9, 355.6] bila perlu.
     */
    private const FORMAT = [215, 330];

    public static function html(Kwitansi $kwitansi): string
    {
        return view('pdf.kwitansi', ['k' => $kwitansi])->render();
    }

    public static function pdf(Kwitansi $kwitansi): string
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $fontDirs = (new ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new FontVariables())->getDefaults()['fontdata'];

        $mpdf = new Mpdf([
            'format' => self::FORMAT,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'tempDir' => $tempDir,
            // Pakai Arial asli (mirip cetakan Excel kantor). Windows/Laragon.
            'fontDir' => array_merge($fontDirs, ['C:\\Windows\\Fonts']),
            'fontdata' => $fontData + [
                'arial' => [
                    'R' => 'arial.ttf',
                    'B' => 'arialbd.ttf',
                    'I' => 'ariali.ttf',
                    'BI' => 'arialbi.ttf',
                ],
            ],
            'default_font' => 'arial',
        ]);

        $mpdf->WriteHTML(self::html($kwitansi));

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    public static function simpan(Kwitansi $kwitansi, string $absolutePath): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($absolutePath, self::pdf($kwitansi));
    }
}
