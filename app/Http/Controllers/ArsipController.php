<?php

namespace App\Http\Controllers;

use App\Models\BerkasFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArsipController extends Controller
{
    public function unduh(BerkasFile $file): StreamedResponse
    {
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404, 'File arsip tidak ditemukan.');

        return Storage::disk($file->disk)->download($file->path, $file->nama_asli);
    }
}
