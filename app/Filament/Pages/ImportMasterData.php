<?php

namespace App\Filament\Pages;

use App\Models\TahunAnggaran;
use App\Services\MasterDataImporter;
use App\Services\MasterExcelTemplate;
use App\Services\MasterImportResult;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ImportMasterData extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Impor Master dari Excel';

    protected static ?string $navigationLabel = 'Impor dari Excel';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected string $view = 'filament.pages.import-master-data';

    protected function getHeaderActions(): array
    {
        return [
            $this->importExcelAction(),
            $this->downloadTemplateAction(),
        ];
    }

    protected function importExcelAction(): Action
    {
        return Action::make('impor')
            ->label('Impor Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->modalHeading('Impor Master Data')
            ->modalSubmitActionLabel('Impor')
            ->schema([
                TextInput::make('tahun')
                    ->label('Tahun Anggaran')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required()
                    ->default(fn () => TahunAnggaran::aktif()?->tahun ?? (int) date('Y')),
                Toggle::make('set_aktif')
                    ->label('Jadikan tahun ini sebagai tahun aktif')
                    ->default(true),
                FileUpload::make('file')
                    ->label('File Excel (.xlsx)')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->disk('local')
                    ->directory('imports-master')
                    ->visibility('private'),
            ])
            ->action(function (array $data): void {
                $relative = is_array($data['file']) ? reset($data['file']) : $data['file'];
                $absolute = Storage::disk('local')->path($relative);

                try {
                    $result = app(MasterDataImporter::class)
                        ->fromFile($absolute, (int) $data['tahun'], (bool) ($data['set_aktif'] ?? true));
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Gagal membaca file')
                        ->body($e->getMessage())
                        ->persistent()
                        ->send();

                    return;
                } finally {
                    Storage::disk('local')->delete($relative);
                }

                $this->notifyResult($result);
            });
    }

    protected function downloadTemplateAction(): Action
    {
        return Action::make('template')
            ->label('Unduh Template')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->schema([
                TextInput::make('tahun')
                    ->label('Tahun (kosongkan untuk template contoh)')
                    ->helperText('Jika diisi & sudah ada data, file akan berisi data tahun tersebut untuk diedit lalu diimpor ulang.')
                    ->numeric(),
            ])
            ->action(function (array $data) {
                $tahun = ($data['tahun'] ?? '') !== '' ? (int) $data['tahun'] : null;
                $spreadsheet = app(MasterExcelTemplate::class)->build($tahun);

                $filename = 'master-'.($tahun ?? 'template').'.xlsx';
                $tmp = tempnam(sys_get_temp_dir(), 'saku_master_').'.xlsx';
                (new XlsxWriter($spreadsheet))->save($tmp);

                return response()->download($tmp, $filename)->deleteFileAfterSend(true);
            });
    }

    protected function notifyResult(MasterImportResult $result): void
    {
        if (! $result->success) {
            $shown = array_slice($result->errors, 0, 12);
            $extra = count($result->errors) - count($shown);
            $body = collect($shown)->map(fn ($e) => e($e))->implode('<br>');
            if ($extra > 0) {
                $body .= '<br><em>… dan '.$extra.' masalah lainnya.</em>';
            }

            Notification::make()
                ->danger()
                ->title('Impor dibatalkan — '.count($result->errors).' masalah, tidak ada data yang disimpan')
                ->body(new HtmlString($body))
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Impor berhasil')
            ->body(new HtmlString(collect($result->summaryLines())->map(fn ($l) => e($l))->implode('<br>')))
            ->persistent()
            ->send();
    }
}
