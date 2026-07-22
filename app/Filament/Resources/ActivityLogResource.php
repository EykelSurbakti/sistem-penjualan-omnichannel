<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Pengaturan & Multi-Outlet';
    protected static ?string $navigationLabel = 'Log Aktivitas & Audit';
    protected static ?string $modelLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas & Audit Sistem';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->check() && is_null(auth()->user()->outlet_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && is_null(auth()->user()->outlet_id);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        // Hanya Master Admin yang bisa hapus log lama jika perlu
        return auth()->check() && !auth()->user()->outlet_id;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->latest();

        if (auth()->check() && auth()->user()->outlet_id) {
            $outletId = auth()->user()->outlet_id;
            $query->where(function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId)->orWhere('user_id', auth()->id());
            });
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/y H:i')
                    ->description(fn (ActivityLog $record): string => $record->created_at?->diffForHumans() ?? '-')
                    ->sortable()
                    ->size('sm'),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Pelaku')
                    ->weight('bold')
                    ->description(fn (ActivityLog $record): string => $record->user_role ?: 'Sistem')
                    ->searchable()
                    ->size('sm'),

                Tables\Columns\TextColumn::make('outlet_name')
                    ->label('Cabang')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Semua Toko (Konsolidasi)' => 'info',
                        default => 'success',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Barang & Stok' => 'warning',
                        'Pesanan & Transaksi' => 'success',
                        'Shift Kasir' => 'danger',
                        default => 'primary',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('action_type')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'RESTORE' => '🔄 Pemulihan',
                        'PRICE_CHANGE' => '💰 Ubah Harga',
                        'UPDATE' => '✏️ Edit',
                        'CREATE' => '➕ Tambah',
                        'DELETE' => '🗑️ Hapus',
                        'STOCK_ADJUSTMENT' => '📦 Mutasi',
                        'SHIFT' => '⏰ Shift',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'RESTORE' => 'success',
                        'PRICE_CHANGE' => 'danger',
                        'UPDATE' => 'info',
                        'CREATE' => 'success',
                        'DELETE' => 'danger',
                        'STOCK_ADJUSTMENT' => 'warning',
                        'SHIFT' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Rincian Aktivitas')
                    ->wrap()
                    ->weight('medium')
                    ->searchable()
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action_type')
                    ->label('Filter Tipe Aksi')
                    ->options([
                        'RESTORE' => '🔄 Pemulihan',
                        'PRICE_CHANGE' => '💰 Ubah Harga',
                        'UPDATE' => '✏️ Pengeditan',
                        'CREATE' => '➕ Penambahan',
                        'DELETE' => '🗑️ Penghapusan',
                        'STOCK_ADJUSTMENT' => '📦 Mutasi Stok',
                        'SHIFT' => '⏰ Shift Kasir',
                    ]),

                Tables\Filters\SelectFilter::make('module')
                    ->label('Filter Modul')
                    ->options([
                        'Barang & Stok' => 'Barang & Stok',
                        'Pesanan & Transaksi' => 'Pesanan & Transaksi',
                        'Shift Kasir' => 'Shift Kasir',
                    ]),

                Tables\Filters\SelectFilter::make('outlet_id')
                    ->label('Filter Cabang')
                    ->relationship('outlet', 'name')
                    ->visible(fn () => !auth()->user()?->outlet_id),
            ])
            ->actions([
                Tables\Actions\Action::make('pulihkan_barang')
                    ->label('Pulihkan')
                    ->button()
                    ->size('xs')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->tooltip('Pulihkan kembali barang terhapus ini ke katalog toko')
                    ->visible(function (ActivityLog $record) {
                        if ($record->action_type !== 'DELETE' || $record->module !== 'Barang & Stok' || empty($record->old_values['sku'])) {
                            return false;
                        }
                        $data = $record->old_values;
                        $query = \App\Models\Product::where('sku', $data['sku']);
                        if (!empty($data['outlet_id'])) {
                            $query->where('outlet_id', $data['outlet_id']);
                        } else {
                            $query->whereNull('outlet_id');
                        }
                        return !$query->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Barang yang Dihapus?')
                    ->modalDescription(fn (ActivityLog $record) => "Apakah Anda yakin ingin memulihkan kembali barang \"" . ($record->old_values['name'] ?? 'Barang') . "\" (SKU: " . ($record->old_values['sku'] ?? '-') . ") ke katalog toko Anda?")
                    ->modalSubmitActionLabel('Ya, Pulihkan Sekarang')
                    ->action(function (ActivityLog $record) {
                        $data = $record->old_values;
                        if (empty($data) || empty($data['sku'])) return;

                        $existingQuery = \App\Models\Product::where('sku', $data['sku']);
                        if (!empty($data['outlet_id'])) {
                            $existingQuery->where('outlet_id', $data['outlet_id']);
                        } else {
                            $existingQuery->whereNull('outlet_id');
                        }
                        $existing = $existingQuery->first();
                        if ($existing) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('SKU Sudah Digunakan')
                                ->body("Barang dengan SKU {$data['sku']} ({$existing->name}) saat ini sudah ada di katalog. Jika ingin memulihkan, silakan ganti atau hapus SKU yang duplikat terlebih dahulu.")
                                ->send();
                            return;
                        }

                        $prod = \App\Models\Product::withoutEvents(function () use ($data) {
                            return \App\Models\Product::create([
                                'outlet_id' => $data['outlet_id'] ?? null,
                                'category_id' => $data['category_id'] ?? null,
                                'sku' => $data['sku'],
                                'name' => $data['name'],
                                'slug' => ($data['slug'] ?? \Illuminate\Support\Str::slug($data['name'])) . '-' . substr(md5(uniqid()), 0, 6),
                                'description' => $data['description'] ?? null,
                                'base_price' => $data['base_price'] ?? 0,
                                'cost_price' => $data['cost_price'] ?? 0,
                                'is_active' => true,
                                'track_inventory' => $data['track_inventory'] ?? true,
                                'alert_at_stock' => $data['alert_at_stock'] ?? 3,
                            ]);
                        });

                        if ($prod && $prod->outlet_id) {
                            \App\Models\Inventory::withoutEvents(function () use ($prod) {
                                return \App\Models\Inventory::firstOrCreate(
                                    ['product_id' => $prod->id, 'outlet_id' => $prod->outlet_id],
                                    ['quantity' => 0]
                                );
                            });
                        }

                        \App\Models\ActivityLog::record(
                            'RESTORE',
                            'Barang & Stok',
                            "Memulihkan kembali barang terhapus ke dalam katalog: '{$prod->name}' (SKU: {$prod->sku}) dengan harga jual Rp " . number_format($prod->base_price, 0, ',', '.'),
                            $prod,
                            $data,
                            $prod->toArray()
                        );

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Barang Berhasil Dipulihkan!')
                            ->body("Barang \"{$prod->name}\" (SKU: {$prod->sku}) telah dikembalikan ke katalog barang toko dengan harga jual Rp " . number_format($prod->base_price, 0, ',', '.') . ".")
                            ->send();
                    }),

                Tables\Actions\Action::make('sudah_dipulihkan')
                    ->label('✓ Pulih')
                    ->button()
                    ->size('xs')
                    ->color('gray')
                    ->disabled()
                    ->tooltip('Barang ini sudah dipulihkan dan saat ini aktif di dalam katalog toko')
                    ->visible(function (ActivityLog $record) {
                        if ($record->action_type !== 'DELETE' || $record->module !== 'Barang & Stok' || empty($record->old_values['sku'])) {
                            return false;
                        }
                        $data = $record->old_values;
                        $query = \App\Models\Product::where('sku', $data['sku']);
                        if (!empty($data['outlet_id'])) {
                            $query->where('outlet_id', $data['outlet_id']);
                        } else {
                            $query->whereNull('outlet_id');
                        }
                        return $query->exists();
                    }),

                Tables\Actions\Action::make('lihat_perubahan')
                    ->label('Rincian')
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->color('primary')
                    ->tooltip('Lihat rincian data sebelum & sesudah')
                    ->modalHeading(fn (ActivityLog $record) => "Rincian Log Audit #{$record->id} - {$record->action_type}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (ActivityLog $record) => view('filament.modals.activity-log-detail', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => !auth()->user()?->outlet_id),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
