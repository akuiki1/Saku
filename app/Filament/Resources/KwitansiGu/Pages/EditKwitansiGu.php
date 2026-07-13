<?php

namespace App\Filament\Resources\KwitansiGu\Pages;

use App\Filament\Resources\KwitansiGu\KwitansiGuResource;
use App\Models\Berkas;
use App\Services\SimpanKwitansiGu;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditKwitansiGu extends EditRecord
{
    protected static string $resource = KwitansiGuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->url(fn (Berkas $record) => route('cetak.kwitansi', $record))
                ->openUrlInNewTab()
                ->visible(fn (Berkas $record) => $record->kwitansi !== null),
            DeleteAction::make(),
        ];
    }

    /**
     * Muat kembali data kwitansi (items, pajak, varian) ke dalam form saat edit.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Berkas $berkas */
        $berkas = $this->record;
        $k = $berkas->kwitansi;

        if ($k === null) {
            return $data;
        }

        $k->loadMissing(['items', 'pajak']);

        $data['uraian_pembayaran'] = $k->uraian_pembayaran;
        $data['penerima_norek'] = $k->snap_penerima_norek;
        $data['total_diterima'] = $k->total_diterima;

        $data['items'] = $k->items
            ->map(fn ($it) => [
                'uraian' => $it->uraian,
                'volume' => (float) $it->volume,
                'satuan' => $it->satuan,
                'harga_satuan' => (int) $it->harga_satuan,
            ])->all();

        $data['pajak'] = $k->pajak
            ->map(fn ($p) => [
                'jenis' => $p->jenis->value,
                'tarif_persen' => (float) $p->tarif_persen,
                'dasar_pengenaan' => (int) $p->dasar_pengenaan,
                'nominal' => (int) $p->nominal,
                'id_billing' => $p->id_billing,
            ])->all();

        $data['jumlah_manual'] = $k->items->isEmpty() ? (int) $k->uang_sejumlah : null;
        $data['varian'] = $k->pajak->isNotEmpty() ? 'pajak' : ($k->items->isNotEmpty() ? 'rincian' : 'polos');

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Berkas $record */
        return app(SimpanKwitansiGu::class)->simpan($data, $record);
    }
}
