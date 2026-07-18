<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Pesanan & Transaksi';
    protected static ?string $navigationLabel = 'Daftar Pesanan';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        // Sembunyikan dari Master Admin (Pemilik). Master Admin menggunakan halaman Pesanan Eksekutif ala iSeller.
        return !is_null(auth()->user()?->outlet_id);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
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
                Forms\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->required(),
                        Forms\Components\Select::make('channel_id')
                            ->label('Channel Penjualan')
                            ->relationship('channel', 'name')
                            ->required(),
                        Forms\Components\Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'name'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Total')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'unpaid' => 'Belum Dibayar',
                                'paid' => 'Lunas',
                                'refunded' => 'Dikembalikan (Refund)',
                            ])
                            ->required(),
                        Forms\Components\Select::make('fulfillment_status')
                            ->label('Status Pemenuhan')
                            ->options([
                                'processing' => 'Diproses',
                                'ready' => 'Siap Diambil/Dikirim',
                                'shipped' => 'Dalam Pengiriman',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Akhir')
                            ->numeric()
                            ->prefix('Rp'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->placeholder('Guest'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'danger' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Belum Bayar',
                        'paid' => 'Lunas',
                        'refunded' => 'Refund',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('fulfillment_status')
                    ->label('Pengiriman')
                    ->colors([
                        'primary' => 'processing',
                        'info' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'processing' => 'Diproses',
                        'ready' => 'Siap',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel_id')
                    ->label('Filter Channel')
                    ->relationship('channel', 'name'),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Bayar')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'paid' => 'Lunas',
                        'refunded' => 'Refund',
                    ]),
            ])
            ->recordUrl(fn (Model $record): string => static::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make()->label('Rincian Pesanan'),
                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->emptyStateHeading('Belum Ada Pesanan')
            ->emptyStateDescription('Pesanan yang dibuat dari POS toko atau channel online akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
