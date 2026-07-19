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
                    ->label('Waktu Aktivitas')
                    ->dateTime('d/m/Y H:i:s')
                    ->description(fn (ActivityLog $record): string => $record->created_at?->diffForHumans() ?? '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Pelaku / Pengguna')
                    ->weight('bold')
                    ->description(fn (ActivityLog $record): string => $record->user_role ?: 'Sistem')
                    ->searchable(),

                Tables\Columns\TextColumn::make('outlet_name')
                    ->label('Cabang Toko')
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
                    ->label('Tipe Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PRICE_CHANGE' => '💰 Ubah Harga',
                        'UPDATE' => '✏️ Pengeditan',
                        'CREATE' => '➕ Penambahan',
                        'DELETE' => '🗑️ Penghapusan',
                        'STOCK_ADJUSTMENT' => '📦 Mutasi Stok',
                        'SHIFT' => '⏰ Shift Kasir',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
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
                Tables\Actions\Action::make('lihat_perubahan')
                    ->label('Rincian Data')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
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
