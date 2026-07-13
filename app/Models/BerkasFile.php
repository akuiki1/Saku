<?php

namespace App\Models;

use App\Enums\JenisFileArsip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BerkasFile extends Model
{
    protected $table = 'berkas_file';

    protected $fillable = [
        'berkas_id', 'jenis', 'nama_asli', 'path', 'disk', 'mime', 'ukuran', 'keterangan',
    ];

    protected $casts = [
        'jenis' => JenisFileArsip::class,
        'ukuran' => 'integer',
    ];

    protected static function booted(): void
    {
        // Lengkapi metadata (disk/ukuran/mime) otomatis dari file tersimpan.
        static::saving(function (BerkasFile $file) {
            $disk = $file->disk ?: 'local';
            $file->disk = $disk;

            if ($file->path && Storage::disk($disk)->exists($file->path)) {
                $file->ukuran ??= Storage::disk($disk)->size($file->path);
                $file->mime ??= Storage::disk($disk)->mimeType($file->path);
            }
        });

        // Hapus file fisik saat record dihapus lewat model (mis. aksi Delete).
        static::deleting(function (BerkasFile $file) {
            if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                Storage::disk($file->disk)->delete($file->path);
            }
        });
    }

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class);
    }

    public function ukuranManusia(): string
    {
        $bytes = (int) $this->ukuran;
        if ($bytes <= 0) {
            return '—';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    }
}
