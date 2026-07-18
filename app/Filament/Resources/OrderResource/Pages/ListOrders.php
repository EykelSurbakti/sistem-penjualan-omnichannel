<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Pesanan'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Pesanan'),
            'belum_difulfill' => Tab::make('Belum Difulfill')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('fulfillment_status', ['processing', 'ready'])),
            'belum_dibayar' => Tab::make('Belum Dibayar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', 'unpaid')),
            'lunas' => Tab::make('Lunas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', 'paid')),
        ];
    }
}
