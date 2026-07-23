<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Katalog & Inventaris';
    protected static ?string $navigationLabel = 'Daftar Barang & Stok';
    protected static ?string $modelLabel = 'Barang';
    protected static ?string $pluralModelLabel = 'Daftar Barang & Stok Toko';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['category']);
        if (auth()->check() && auth()->user()->outlet_id) {
            $query->where('outlet_id', auth()->user()->outlet_id);
        }
        $query->withSum('inventories as total_qty', 'quantity');
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Barang')
                    ->description('Masukkan rincian barang yang akan dijual di kasir dan toko online')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Barang')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Kategori')
                                    ->required(),
                            ])
                            ->placeholder('Pilih Kategori...'),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU / Kode Barang')
                            ->required()
                            ->placeholder('Contoh: KPK-7808')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Barang')
                            ->required()
                            ->placeholder('Contoh: Kotak Pensil Kaleng')
                            ->maxLength(255),
                        Forms\Components\Hidden::make('slug')
                            ->default(fn () => 'temp-' . uniqid()),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Tambahan')
                            ->placeholder('Keterangan singkat barang (opsional)')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Harga Jual & Pengaturan Stok')
                    ->description('Atur harga jual toko serta stok persediaan cabang')
                    ->schema([
                        Forms\Components\Select::make('outlet_id')
                            ->label('Cabang Toko')
                            ->relationship('outlet', 'name')
                            ->required()
                            ->default(fn () => auth()->user()?->outlet_id)
                            ->disabled(fn () => auth()->user()?->outlet_id !== null)
                            ->dehydrated()
                            ->visible(fn () => auth()->user()?->outlet_id === null),
                        Forms\Components\TextInput::make('qty')
                            ->label('Stok Fisik Awal')
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->placeholder('0')
                            ->default(0)
                            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 0 : (int) str_replace(',', '', (string) $state))
                            ->prefix('Pcs')
                            ->extraInputAttributes(['onfocus' => 'this.select()']),
                        Forms\Components\TextInput::make('base_price')
                            ->label('Harga Jual Toko (Rp)')
                            ->required()
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->default(null)
                            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 0 : (float) str_replace(',', '', (string) $state))
                            ->extraInputAttributes(['onfocus' => 'this.select()']),
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Harga Pokok Modal / HPP (Rp)')
                            ->required()
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->default(null)
                            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 0 : (float) str_replace(',', '', (string) $state))
                            ->extraInputAttributes(['onfocus' => 'this.select()']),
                        Forms\Components\Select::make('soldout_strategy')
                            ->label('Saat Stok Fisik Habis')
                            ->options([
                                'stop'     => 'Berhenti Jual (Sembunyikan dari Layar Kasir)',
                                'continue' => 'Tetap Jual (Pesanan Menunggu / Backorder)',
                            ])
                            ->default('stop')
                            ->required(),
                        Forms\Components\Toggle::make('has_variants')
                            ->label('Memiliki Varian Ukuran/Warna')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif Dijual di Kasir')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            // ── iSeller logic: terbaru dulu (Tanggal Dibuat DESC) ──
            ->defaultSort('created_at', 'desc')
            // ── 50 per halaman, persis iSeller ──
            ->defaultPaginationPageOption(50)
            ->paginationPageOptions([50, 100, 200])
            ->searchPlaceholder('Cari nama barang atau kode SKU...')
            ->columns([
                // Kolom 1: Nama + SKU gabung (persis iSeller)
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->sku ?? '-'),

                // Kolom 2: Inventaris — teks pcs dalam stok / Stok habis (persis iSeller)
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Inventaris')
                    ->formatStateUsing(function ($state): string {
                        $qty = (int) ($state ?? 0);
                        return $qty > 0 ? "{$qty} pcs dalam stok" : 'Stok habis';
                    })
                    ->color(fn ($state): string => ((int)($state ?? 0)) > 0 ? 'success' : 'danger')
                    ->sortable(false),

                // Kolom 3: Harga
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('semibold'),

                // Kolom 4: Kategori
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('-'),

                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Toko/Cabang')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                // Kolom 5: Tanggal Dibuat (persis iSeller)
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Filter Status')
                    ->trueLabel('Hanya Aktif Dijual')
                    ->falseLabel('Hanya Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit Cepat')
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->action(function (Product $record) {
                        $record->delete();
                        \Filament\Notifications\Notification::make()
                            ->title('Barang Dihapus')
                            ->body("Barang \"{$record->name}\" berhasil dihapus dari cabang ini.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $deletedCount = 0;
                            foreach ($records as $product) {
                                $product->delete();
                                $deletedCount++;
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Barang Terhapus')
                                ->body("{$deletedCount} barang berhasil dihapus dari cabang masing-masing.")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Barang Terdaftar')
            ->emptyStateDescription('Tekan tombol + Tambah Barang Baru di atas untuk mulai menambahkan katalog barang toko Anda.')
            ->emptyStateIcon('heroicon-o-cube');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
