<?php

namespace Tests\Unit;

use App\Services\Terbilang;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class TerbilangTest extends TestCase
{
    /**
     * @dataProvider rupiahProvider
     */
    public function test_rupiah(int $nominal, string $harapan): void
    {
        $this->assertSame($harapan, Terbilang::rupiah($nominal));
    }

    public static function rupiahProvider(): array
    {
        return [
            'nol' => [0, 'nol rupiah'],
            'sebelas' => [11, 'sebelas rupiah'],
            'dua puluh satu' => [21, 'dua puluh satu rupiah'],
            'seratus' => [100, 'seratus rupiah'],
            'seratus lima puluh' => [150, 'seratus lima puluh rupiah'],
            'seribu' => [1000, 'seribu rupiah'],
            'seribu lima ratus' => [1500, 'seribu lima ratus rupiah'],
            'dua ribu' => [2000, 'dua ribu rupiah'],
            'satu juta' => [1_000_000, 'satu juta rupiah'],
            // Nominal nyata dari kwitansi makan minum (GU)
            'makan minum' => [1_925_000, 'satu juta sembilan ratus dua puluh lima ribu rupiah'],
            // Contoh dari PRD
            'prd' => [1_234_500, 'satu juta dua ratus tiga puluh empat ribu lima ratus rupiah'],
            // Nominal nyata dari SP2D LS (CV Stand Alone)
            'ls sp2d' => [239_332_200, 'dua ratus tiga puluh sembilan juta tiga ratus tiga puluh dua ribu dua ratus rupiah'],
        ];
    }

    public function test_tanggal_legal_bap(): void
    {
        // 15 Juni 2026 = Senin (sesuai BAP CV Stand Alone yang discan)
        $tanggal = Carbon::create(2026, 6, 15);

        $this->assertSame(
            'Pada hari ini, Senin Tanggal Lima Belas Bulan Juni Tahun Dua Ribu Dua Puluh Enam',
            Terbilang::tanggalLegal($tanggal),
        );
    }

    public function test_tanggal_legal_awal_bulan(): void
    {
        // 1 Januari 2026 = Kamis
        $tanggal = Carbon::create(2026, 1, 1);

        $this->assertSame(
            'Pada hari ini, Kamis Tanggal Satu Bulan Januari Tahun Dua Ribu Dua Puluh Enam',
            Terbilang::tanggalLegal($tanggal),
        );
    }
}
