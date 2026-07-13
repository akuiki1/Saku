<?php

namespace App\Filament\Resources\KwitansiGu\RelationManagers;

use App\Enums\TahapanBerkas;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TahapanRelationManager extends RelationManager
{
    protected static string $relationship = 'tahapan';

    protected static ?string $title = 'Pelacakan Status';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-clock';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tahapan')
                    ->label('Tahapan')
                    ->options(TahapanBerkas::class)
                    ->required()
                    ->helperText('Status berkas otomatis mengikuti tahapan terbaru.'),
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),
                Textarea::make('keterangan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahapan')
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('tahapan')
                    ->badge(),
                TextColumn::make('keterangan')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Tahapan'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
