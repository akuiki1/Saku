<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Services\PdfKwitansi;
use Symfony\Component\HttpFoundation\Response;

class CetakController extends Controller
{
    public function kwitansi(Berkas $berkas): Response
    {
        $kwitansi = $berkas->kwitansi;
        abort_if($kwitansi === null, 404, 'Berkas ini belum punya kwitansi.');

        $pdf = PdfKwitansi::pdf($kwitansi);
        $nama = 'kwitansi-'.($berkas->no_bku ? str_replace(['/', '\\'], '-', $berkas->no_bku) : $berkas->id).'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nama.'"',
        ]);
    }
}
