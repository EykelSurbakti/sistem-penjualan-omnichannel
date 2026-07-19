<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PanduanAplikasi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Panduan POS';
    protected static ?string $title = '📖 Buku Panduan Penggunaan Muliku Store POS';
    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.panduan-aplikasi';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }
}
