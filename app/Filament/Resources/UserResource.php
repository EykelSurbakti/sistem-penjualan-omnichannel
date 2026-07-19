<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Pengaturan & Multi-Outlet';
    protected static ?string $navigationLabel = 'Daftar Pengguna';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->check() && is_null(auth()->user()->outlet_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && is_null(auth()->user()->outlet_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun & Penugasan Toko')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Login')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('role_label')
                            ->label('Label Peran / Posisi')
                            ->placeholder('Contoh: Pemilik Akun / Admin dibatasi, Akses Aplikasi')
                            ->default('Admin dibatasi, Akses Aplikasi'),
                        Forms\Components\Select::make('outlet_id')
                            ->label('Batasi Akses ke Toko / Outlet')
                            ->relationship('outlet', 'name')
                            ->placeholder('Akses Semua Toko MALIKU (Owner Master)')
                            ->helperText('Kosongkan jika pengguna ini adalah Master Owner yang dapat memantau seluruh toko.'),
                    ])->columns(2),

                Forms\Components\Section::make('Batasi Akses Modul Aplikasi')
                    ->description('Pilih modul spesifik yang boleh diakses oleh pengguna ini.')
                    ->schema([
                        Forms\Components\CheckboxList::make('allowed_modules')
                            ->label('Modul yang Diizinkan')
                            ->options([
                                'pos' => 'Point of Sale (POS)',
                                'online_store' => 'Toko Online MALIKU',
                                'dashboard' => 'Dasbor & Grafik Analitik',
                                'orders' => 'Pesanan & Transaksi',
                                'customers' => 'Pelanggan & Loyalti',
                                'products' => 'Katalog Produk',
                                'inventory' => 'Stok & Inventaris',
                                'transfer' => 'Transfer Stok Antar Toko',
                                'discount' => 'Diskon & Promosi',
                                'reports_sales' => 'Laporan Penjualan',
                                'reports_inventory' => 'Laporan Inventaris',
                            ])
                            ->columns(3),
                    ]),

                Forms\Components\Section::make('Pengaturan Sekuritas Lanjutan')
                    ->description('Kontrol keamanan tambahan untuk kasir / staff toko.')
                    ->schema([
                        Forms\Components\CheckboxList::make('security_settings')
                            ->label('Batasan Sekuritas')
                            ->options([
                                'cannot_edit_inventory' => 'Larang mengedit inventaris secara langsung',
                                'cannot_edit_price' => 'Larang mengedit harga jual produk',
                                'cannot_delete_data' => 'Larang penghapusan data transaksi/produk',
                                'cannot_cancel_order' => 'Larang melakukan pembatalan pesanan dan pengembalian dana (refund)',
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role_label')
                    ->label('Peran')
                    ->badge()
                    ->color(fn ($state) => str_contains(strtolower((string)$state), 'owner') ? 'primary' : 'info'),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Toko / Outlet')
                    ->default('Semua Outlet MALIKU')
                    ->badge()
                    ->color(fn ($state) => $state === 'Semua Outlet MALIKU' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y (H:i)')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->emptyStateHeading('Belum Ada Pengguna Terdaftar')
            ->emptyStateDescription('Daftarkan kasir atau staf pengelola untuk masing-masing toko MALIKU Anda di sini.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
