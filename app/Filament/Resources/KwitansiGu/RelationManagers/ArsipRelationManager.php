<?php

namespace App\Filament\Resources\KwitansiGu\RelationManagers;

use App\Enums\JenisFileArsip;
use App\Models\BerkasFile;
use App\Services\PathArsip;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArsipRelationManager extends RelationManager
{
    protected static string $relationship = 'arsip';

    protected static ?string $title = 'Arsip Digital';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-paper-clip';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jenis')
                    ->label('Jenis dokumen')
                    ->options(JenisFileArsip::class)
                    ->default(JenisFileArsip::ScanFinal->value)
                    ->required(),
                FileUpload::make('path')
                    ->label('File (PDF / gambar, maks 20 MB)')
                    ->disk('local')
                    ->directory(fn () => PathArsip::direktori($this->getOwnerRecord()))
                    ->getUploadedFileNameForStorageUsing(fn ($file) => PathArsip::namaFile($file->getClientOriginalName()))
                    ->storeFileNamesIn('nama_asli')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(20480)
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('keterangan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_asli')
            ->columns([
                TextColumn::make('nama_asli')
                    ->label('Nama File')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('jenis')
                    ->badge(),
                TextColumn::make('ukuran')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (BerkasFile $record) => $record->ukuranManusia())
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()->label('Unggah File'),
            ])
            ->recordActions([
                Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (BerkasFile $record) => route('arsip.unduh', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
