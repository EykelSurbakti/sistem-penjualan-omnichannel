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
            $outletId = auth()->user()->outlet_id;
            $query->where(function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId)->orWhereNull('outlet_id');
            })
            ->withSum(['inventories as total_qty' => fn ($q) => $q->where('outlet_id', $outletId)], 'quantity');
        } else {
            $query->withSum('inventories as total_qty', 'quantity');
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $outlets = \App\Models\Outlet::orderBy('id')->get();
        $outletSchemas = [];
        foreach ($outlets as $outlet) {
            $outletSchemas[] = Forms\Components\TextInput::make('qty_outlet_' . $outlet->id)
                ->label('Stok di ' . $outlet->name)
                ->numeric()
                ->mask(\Filament\Support\RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->placeholder('0')
                ->default(null)
                ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 0 : (int) str_replace(',', '', (string) $state))
                ->prefix('Pcs')
                ->visible(fn () => !auth()->user()?->outlet_id)
                ->extraInputAttributes(['onfocus' => 'this.select()']);
        }

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
                    ->schema(array_merge([
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
                        Forms\Components\TextInput::make('qty_user_outlet')
                            ->label(fn () => 'Stok di ' . (auth()->user()?->outlet?->name ?: 'Cabang Anda'))
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->placeholder('0')
                            ->default(null)
                            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? 0 : (int) str_replace(',', '', (string) $state))
                            ->prefix('Pcs')
                            ->visible(fn () => (bool) auth()->user()?->outlet_id)
                            ->extraInputAttributes(['onfocus' => 'this.select()']),
                    ], $outletSchemas, [
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
                    ]))->columns(2),
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

                // Kolom 4: Kategori (menggantikan Tipe di iSeller)
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('Umum'),

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
                    ->action(function (Product $record, $livewire) {
                        $activeOutletScope = (auth()->check() && auth()->user()->outlet_id) 
                            ? auth()->user()->outlet_id 
                            : (isset($livewire->bulkOutletId) && $livewire->bulkOutletId ? $livewire->bulkOutletId : (isset($livewire->outletId) && $livewire->outletId ? $livewire->outletId : null));
                        if ($activeOutletScope) {
                            $hasOtherStores = \App\Models\Inventory::where('product_id', $record->id)
                                ->where('outlet_id', '!=', $activeOutletScope)
                                ->exists();
                            if ($hasOtherStores || ($record->outlet_id && $record->outlet_id != $activeOutletScope)) {
                                \App\Models\Inventory::where('product_id', $record->id)
                                    ->where('outlet_id', $activeOutletScope)
                                    ->delete();
                                if ($record->outlet_id == $activeOutletScope) {
                                    $record->outlet_id = null;
                                    $record->save();
                                }
                                $outletName = \App\Models\Outlet::find($activeOutletScope)?->name ?? 'Toko Ini';
                                \Filament\Notifications\Notification::make()
                                    ->title("Barang Dilepas dari {$outletName}")
                                    ->body("Barang \"{$record->name}\" dihapus dari {$outletName} (cabang toko lain yang memiliki barang ini tetap utuh & aman).")
                                    ->success()
                                    ->send();
                                return;
                            }
                        }
                        $record->delete();
                        \Filament\Notifications\Notification::make()
                            ->title('Barang Dihapus')
                            ->body("Barang \"{$record->name}\" berhasil dihapus.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, $livewire) {
                            $activeOutletScope = (auth()->check() && auth()->user()->outlet_id) 
                                ? auth()->user()->outlet_id 
                                : (isset($livewire->bulkOutletId) && $livewire->bulkOutletId ? $livewire->bulkOutletId : (isset($livewire->outletId) && $livewire->outletId ? $livewire->outletId : null));
                            $deletedCount = 0;
                            foreach ($records as $product) {
                                if ($activeOutletScope) {
                                    $hasOtherStores = \App\Models\Inventory::where('product_id', $product->id)
                                        ->where('outlet_id', '!=', $activeOutletScope)
                                        ->exists();
                                    if ($hasOtherStores || ($product->outlet_id && $product->outlet_id != $activeOutletScope)) {
                                        \App\Models\Inventory::where('product_id', $product->id)
                                            ->where('outlet_id', $activeOutletScope)
                                            ->delete();
                                        if ($product->outlet_id == $activeOutletScope) {
                                            $product->outlet_id = null;
                                            $product->save();
                                        }
                                        $deletedCount++;
                                        continue;
                                    }
                                }
                                $product->delete();
                                $deletedCount++;
                            }
                            $outletName = $activeOutletScope ? (\App\Models\Outlet::find($activeOutletScope)?->name ?? 'Cabang Terpilih') : 'Katalog Global';
                            \Filament\Notifications\Notification::make()
                                ->title('Penghapusan Berhasil')
                                ->body("Sebanyak {$deletedCount} produk telah dihapus/dilepas dari {$outletName}.")
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
