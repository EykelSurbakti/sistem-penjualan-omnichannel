<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Katalog & Inventaris';
    protected static ?string $navigationLabel = 'Inventaris per Toko';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->outlet_id) {
            $query->where('outlet_id', auth()->user()->outlet_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penyesuaian Stok Toko')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('outlet_id')
                            ->label('Toko / Outlet MALIKU')
                            ->relationship('outlet', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Unit dalam Persediaan (Pcs)')
                            ->required()
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->placeholder('0')
                            ->default(null)
                            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 0 : (int) str_replace(',', '', (string) $state))
                            ->extraInputAttributes(['onfocus' => 'this.select()']),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->label('Ambang Batas Stok Sedikit')
                            ->required()
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->placeholder('5')
                            ->default(5)
                            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 5 : (int) str_replace(',', '', (string) $state))
                            ->extraInputAttributes(['onfocus' => 'this.select()']),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product / Judul Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Toko MALIKU')
                    ->badge()
                    ->color('info')
                    ->visible(fn () => !auth()->user()?->outlet_id)
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('product.soldout_strategy')
                    ->label('Soldout Strategy')
                    ->colors([
                        'danger' => 'stop',
                        'success' => 'continue',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'stop' => 'Berhenti menjual',
                        'continue' => 'Tetap jual (Backorder)',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity (Persediaan)')
                    ->suffix(' pcs')
                    ->badge()
                    ->color(fn (Inventory $record): string => $record->quantity <= $record->low_stock_threshold ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('outlet_id')
                    ->label('Filter Toko MALIKU')
                    ->relationship('outlet', 'name')
                    ->visible(fn () => !auth()->user()?->outlet_id),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Sesuaikan Stok'),
            ])
            ->emptyStateHeading('Data Inventaris Belum Ada')
            ->emptyStateDescription('Stok produk untuk setiap cabang Muliku Store akan ditampilkan di sini.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
