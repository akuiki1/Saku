@php
    /** @var \App\Models\Kwitansi $k */
    $rp = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    $items = $k->relationLoaded('items') ? $k->items : collect();
    $pajak = $k->relationLoaded('pajak') ? $k->pajak : collect();

    $ppn = (int) ($pajak->firstWhere('jenis', \App\Enums\JenisPajak::PPN)?->nominal ?? 0);
    $pphRow = $pajak->first(fn ($p) => in_array($p->jenis, [
        \App\Enums\JenisPajak::PPh22, \App\Enums\JenisPajak::PPh21,
        \App\Enums\JenisPajak::PPh23, \App\Enums\JenisPajak::PPh4_2,
    ], true));
    $pph = (int) ($pphRow?->nominal ?? 0);
    $adaRincianPajak = $pajak->isNotEmpty();
    $sisa = (int) $k->uang_sejumlah - $ppn;
    $total = (int) $k->total_diterima;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: Arial, sans-serif; font-size: 10.5pt; color: #000; }
        body { margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; }
        .label { width: 165px; }
        .sep { width: 10px; }
        .judul { text-align: center; font-size: 15pt; letter-spacing: 1px; margin: 6px 0 10px; }
        .hr { border-top: 1px solid #000; margin: 4px 0; }
        .b { font-weight: bold; }
        .i { font-style: italic; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .ttd-space { height: 62px; }
        .kecil { font-size: 9.5pt; }
    </style>
</head>
<body>

{{-- ================= KOP / HEADER ================= --}}
<table>
    <tr>
        <td style="width: 68%;">
            <table>
                <tr><td class="label">Tahun Anggaran</td><td class="sep">:</td><td>{{ $k->snap_tahun }}</td></tr>
                <tr><td class="label">Kode / Nama Program</td><td class="sep">:</td><td>({{ $k->snap_program_kode }}) {{ $k->snap_program_nama }}</td></tr>
                <tr><td class="label">Kode / Nama Kegiatan</td><td class="sep">:</td><td>({{ $k->snap_kegiatan_kode }}) {{ $k->snap_kegiatan_nama }}</td></tr>
                <tr><td class="label">Kode / Nama Sub Kegiatan</td><td class="sep">:</td><td>({{ $k->snap_subkeg_kode }}) {{ $k->snap_subkeg_nama }}</td></tr>
                <tr><td class="label">Kode / Nama Rekening</td><td class="sep">:</td><td>{{ $k->snap_rekening_kode }} {{ $k->snap_rekening_nama }}</td></tr>
            </table>
        </td>
        <td style="width: 32%; padding-left: 12px;">
            <table>
                <tr><td style="width: 70px;">Kuitansi</td><td class="sep">:</td><td>&nbsp;</td></tr>
                <tr><td>No. BKU</td><td class="sep">:</td><td>&nbsp;</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="hr"></div>

<div class="judul">KUITANSI / BUKTI PEMBAYARAN</div>

{{-- ================= ISI ================= --}}
<table>
    <tr>
        <td class="label">Sudah terima dari</td><td class="sep">:</td>
        <td>Kuasa Pengguna Anggaran Bidang Cipta Karya dan Penataan Ruang Dinas Pekerjaan Umum dan Penataan Ruang Kabupaten Hulu Sungai Tengah Tahun Anggaran {{ $k->snap_tahun }}</td>
    </tr>
    <tr>
        <td class="label">Jumlah Uang</td><td class="sep">:</td>
        <td class="b">{{ $rp($k->uang_sejumlah) }}</td>
    </tr>
    <tr>
        <td class="label">Terbilang</td><td class="sep">:</td>
        <td class="i">( {{ ucwords($k->terbilang) }} )</td>
    </tr>
</table>

@if ($adaRincianPajak)
<div style="margin-top: 8px;"><span class="b">Rincian Pembayaran</span></div>
<table>
    <tr><td class="label">Uang sejumlah</td><td class="sep">:</td><td>{{ $rp($k->uang_sejumlah) }}</td></tr>
    <tr><td class="label">PPn</td><td class="sep">:</td><td>{{ $rp($ppn) }}</td></tr>
    <tr><td class="label">Sisa</td><td class="sep">:</td><td>{{ $rp($sisa) }}</td></tr>
    <tr>
        <td class="label">{{ $pphRow ? $pphRow->jenis->getLabel() . ' (' . rtrim(rtrim(number_format((float) $pphRow->tarif_persen, 2, ',', '.'), '0'), ',') . '%)' : 'PPh' }}</td>
        <td class="sep">:</td><td>{{ $rp($pph) }}</td>
    </tr>
    <tr><td class="label">Total diterima</td><td class="sep">:</td><td>{{ $rp($total) }}</td></tr>
</table>
@endif

<table style="margin-top: 8px;">
    <tr>
        <td class="label">Untuk Pembayaran</td><td class="sep">:</td>
        <td>{{ $k->uraian_pembayaran }}</td>
    </tr>
</table>

@if ($items->isNotEmpty())
<table style="margin-top: 6px;">
    @foreach ($items as $it)
        <tr>
            <td style="width: 90px; padding-left: 20px;">{{ $it->uraian }}</td>
            <td class="sep">:</td>
            <td class="nowrap">{{ rtrim(rtrim(number_format((float) $it->volume, 2, ',', '.'), '0'), ',') }} {{ $it->satuan }}
                &nbsp; X &nbsp; {{ $rp($it->harga_satuan) }} &nbsp; = &nbsp; {{ $rp($it->jumlah) }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="padding-left: 20px;" class="b">Jumlah Belanja</td>
        <td class="sep">:</td>
        <td class="b">{{ $rp($items->sum('jumlah')) }}</td>
    </tr>
</table>
@endif

{{-- ================= TANDA TANGAN ================= --}}
<table style="margin-top: 18px;">
    <tr>
        <td style="width: 50%;">&nbsp;</td>
        <td style="width: 50%;" class="center">Barabai,</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="center">Yang Menerima,</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="ttd-space">&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="center b">{{ $k->snap_penerima_nama }}</td>
    </tr>
    @if ($k->snap_penerima_norek)
    <tr>
        <td>&nbsp;</td>
        <td class="center">No. Rek {{ $k->snap_penerima_norek }}</td>
    </tr>
    @endif
</table>

<table style="margin-top: 10px;">
    <tr>
        <td style="width: 50%;" class="center">Pejabat Pelaksana Teknis Kegiatan,</td>
        <td style="width: 50%;" class="center">Setuju dan Lunas dibayar tanggal :<br>Bendahara Pengeluaran Pembantu,</td>
    </tr>
    <tr>
        <td class="ttd-space">&nbsp;</td>
        <td class="ttd-space">&nbsp;</td>
    </tr>
    <tr>
        <td class="center b">{{ $k->snap_pptk_nama }}</td>
        <td class="center b">{{ $k->snap_bendahara_nama }}</td>
    </tr>
    <tr>
        <td class="center">@if ($k->snap_pptk_nip) NIP. {{ $k->snap_pptk_nip }} @endif</td>
        <td class="center">@if ($k->snap_bendahara_nip) NIP. {{ $k->snap_bendahara_nip }} @endif</td>
    </tr>
</table>

<div class="hr" style="margin-top: 12px;"></div>
<div class="kecil">Barang/Pekerjaan tersebut telah diterima / diselesaikan dengan lengkap dan baik</div>

</body>
</html>
