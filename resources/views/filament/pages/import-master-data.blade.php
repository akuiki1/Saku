<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Cara kerja</x-slot>
        <x-slot name="description">Satu file Excel memuat seluruh data master untuk satu tahun anggaran.</x-slot>

        <ol class="list-decimal ms-5 space-y-1 text-sm">
            <li><strong>Unduh Template</strong> — isi tahun untuk mengekspor data yang sudah ada (siap diedit), atau kosongkan untuk template contoh.</li>
            <li>Isi tiap sheet di Excel. Jangan ubah nama sheet &amp; baris header.</li>
            <li><strong>Impor Excel</strong> — pilih tahun anggaran lalu unggah file <code>.xlsx</code>.</li>
            <li>Impor bersifat <strong>idempoten</strong>: baris yang sudah ada diperbarui, bukan digandakan — aman diulang untuk koreksi.</li>
        </ol>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Struktur file (6 sheet)</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-start">
                        <th class="py-1 pe-4 text-start font-semibold">Sheet</th>
                        <th class="py-1 pe-4 text-start font-semibold">Kolom</th>
                        <th class="py-1 text-start font-semibold">Relasi</th>
                    </tr>
                </thead>
                <tbody class="align-top">
                    <tr><td class="py-1 pe-4 font-mono">Program</td><td class="py-1 pe-4">kode, nama</td><td class="py-1">—</td></tr>
                    <tr><td class="py-1 pe-4 font-mono">Kegiatan</td><td class="py-1 pe-4">kode, nama, program_kode</td><td class="py-1">program_kode → Program</td></tr>
                    <tr><td class="py-1 pe-4 font-mono">KodeRekening</td><td class="py-1 pe-4">kode, uraian</td><td class="py-1">—</td></tr>
                    <tr><td class="py-1 pe-4 font-mono">Pegawai</td><td class="py-1 pe-4">nama, nip, no_rekening, jabatan, golongan, bank</td><td class="py-1">—</td></tr>
                    <tr><td class="py-1 pe-4 font-mono">SubKegiatan</td><td class="py-1 pe-4">kode, nama, kegiatan_kode, pptk_nama, pagu</td><td class="py-1">kegiatan_kode → Kegiatan, pptk_nama → Pegawai</td></tr>
                    <tr><td class="py-1 pe-4 font-mono">Pejabat</td><td class="py-1 pe-4">peran, pegawai_nama</td><td class="py-1">pegawai_nama → Pegawai</td></tr>
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            Catatan: <strong>SubKegiatan</strong> &amp; <strong>Pejabat</strong> disimpan per tahun anggaran; entitas lain dipakai lintas tahun.
            Format kolom <code>nip</code> sebagai <em>Text</em> di Excel agar digit panjang tidak rusak.
        </p>
    </x-filament::section>
</x-filament-panels::page>
