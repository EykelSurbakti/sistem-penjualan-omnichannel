<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class PosKasir extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Pesanan & Transaksi';
    protected static ?string $navigationLabel = 'Layar Kasir (POS)';
    protected static ?string $title = 'Layar Mesin Kasir MALIKU';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.pos-kasir';

    public static function shouldRegisterNavigation(): bool
    {
        // Untuk Master Admin/Pemilik (tanpa outlet_id), sembunyikan POS agar fokus pada Monitoring Eksekutif
        return !is_null(auth()->user()?->outlet_id);
    }

    public function getHeading(): string
    {
        return '';
    }

    // Filter & Search states
    public string $search = '';
    public ?int $selectedCategory = null;
    public ?int $selectedOutletId = null;

    // Cart state: array of ['product_id' => int, 'name' => string, 'sku' => string, 'price' => float, 'quantity' => int, 'max_stock' => int]
    public array $cart = [];

    // Customer & Payment states
    public ?int $customerId = null;
    public float $cashReceived = 0;

    // Success & Confirmation Modal states
    public bool $showSuccessModal = false;
    public bool $showClearCartModal = false;
    public ?array $lastOrderSummary = null;

    public function mount()
    {
        if (auth()->check() && !is_null(auth()->user()->outlet_id)) {
            $activeShift = \App\Models\ShiftSession::where('user_id', auth()->id())
                ->where('status', 'open')
                ->latest()
                ->first();
            if (!$activeShift) {
                return redirect()->to('/portal-kasir?auto_open_shift=1');
            }
        }
        // Default to user's assigned outlet, or first outlet if Master Owner
        $this->selectedOutletId = auth()->user()->outlet_id ?? Outlet::first()?->id;
    }

    public function selectCategory(?int $categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function addToCart(int $productId)
    {
        $product = Product::select('id', 'name', 'sku', 'base_price', 'is_active', 'soldout_strategy')->find($productId);
        if (! $product || ! $product->is_active) {
            return;
        }

        // Check stock in current outlet lightweight
        $stockAvailable = (int) Inventory::where('product_id', $productId)
            ->where('outlet_id', $this->selectedOutletId)
            ->value('quantity');

        if ($stockAvailable <= 0 && $product->soldout_strategy === 'stop') {
            Notification::make()
                ->title('Stok Habis')
                ->body('Produk ini kehabisan stok di toko ini.')
                ->danger()
                ->send();
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] >= $stockAvailable && $product->soldout_strategy === 'stop') {
                Notification::make()
                    ->title('Stok Tidak Mencukupi')
                    ->body('Stok tersedia hanya ' . $stockAvailable . ' pcs.')
                    ->warning()
                    ->send();
                return;
            }
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->base_price,
                'quantity' => 1,
                'max_stock' => $stockAvailable,
            ];
        }

        unset($this->subtotal, $this->total, $this->changeDue, $this->quickCashSuggestions);
        if ($this->cashReceived < $this->getTotalProperty()) {
            $this->cashReceived = $this->getTotalProperty();
        }
    }

    public function incrementQuantity(int $productId)
    {
        if (isset($this->cart[$productId])) {
            $this->addToCart($productId);
        }
    }

    public function updateQuantity(int $productId, int $change)
    {
        if (!isset($this->cart[$productId])) {
            return;
        }

        if ($change > 0) {
            $this->addToCart($productId);
        } else {
            $this->cart[$productId]['quantity'] += $change;
            if ($this->cart[$productId]['quantity'] <= 0) {
                unset($this->cart[$productId]);
            }
            unset($this->subtotal, $this->total, $this->changeDue, $this->quickCashSuggestions);
        }
    }

    public function decrementQuantity(int $productId)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']--;
            if ($this->cart[$productId]['quantity'] <= 0) {
                unset($this->cart[$productId]);
            }
            unset($this->subtotal, $this->total, $this->changeDue, $this->quickCashSuggestions);
        }
    }

    public function removeFromCart(int $productId)
    {
        unset($this->cart[$productId]);
        unset($this->subtotal, $this->total, $this->changeDue, $this->quickCashSuggestions);
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->cashReceived = 0;
        $this->showClearCartModal = false;
        unset($this->subtotal, $this->total, $this->changeDue, $this->quickCashSuggestions);
    }

    public function setQuickCash(float $amount)
    {
        $this->cashReceived = $amount;
        unset($this->changeDue);
    }

    public function setExactCash()
    {
        $this->cashReceived = $this->getTotalProperty();
        unset($this->changeDue);
    }

    #[Computed]
    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    #[Computed]
    public function getTaxProperty(): float
    {
        return 0;
    }

    #[Computed]
    public function getTotalProperty(): float
    {
        return $this->getSubtotalProperty();
    }

    #[Computed]
    public function getChangeDueProperty(): float
    {
        $change = $this->cashReceived - $this->getTotalProperty();
        return $change > 0 ? $change : 0;
    }

    #[Computed]
    public function getQuickCashSuggestionsProperty(): array
    {
        $total = $this->getTotalProperty();
        if ($total <= 0) {
            return [50000, 100000, 150000, 200000];
        }

        $suggestions = [$total];
        $ceil10k = ceil($total / 10000) * 10000;
        if ($ceil10k > $total && !in_array($ceil10k, $suggestions)) {
            $suggestions[] = $ceil10k;
        }
        $ceil50k = ceil($total / 50000) * 50000;
        if ($ceil50k > $total && !in_array($ceil50k, $suggestions)) {
            $suggestions[] = $ceil50k;
        }
        $ceil100k = ceil($total / 100000) * 100000;
        if ($ceil100k > $total && !in_array($ceil100k, $suggestions)) {
            $suggestions[] = $ceil100k;
        }
        if (count($suggestions) < 4) {
            $suggestions[] = $total + 50000;
        }
        if (count($suggestions) < 4) {
            $suggestions[] = $total + 100000;
        }

        return array_slice(array_unique($suggestions), 0, 4);
    }

    #[Computed]
    public function getProductsProperty()
    {
        $outletId = $this->selectedOutletId;

        $query = Product::query()
            ->where(function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId)->orWhereNull('outlet_id');
            })
            ->where('is_active', true)
            ->select('products.id', 'products.name', 'products.sku', 'products.base_price', 'products.category_id')
            ->withSum(['inventories as stock_in_outlet' => function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            }], 'quantity');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        // Hanya tampilkan produk yang stok cabangnya > 0, urutkan dari barang terlaris / sering terjual (sales_count), lalu abjad
        return $query->having('stock_in_outlet', '>', 0)
            ->orderByDesc('products.sales_count')
            ->orderBy('products.name')
            ->limit(120)
            ->get();
    }

    #[Computed]
    public function getCategoriesProperty()
    {
        return Category::where('is_active', true)->get();
    }

    #[Computed]
    public function getCustomersProperty()
    {
        return Customer::select('id', 'name')->get();
    }

    #[Computed]
    public function getOutletsProperty()
    {
        return Outlet::select('id', 'name')->where('is_active', true)->get();
    }

    #[Computed]
    public function getActiveShiftProperty()
    {
        return \App\Models\ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    public function processCashPayment()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Belanja Kosong')
                ->warning()
                ->send();
            return;
        }

        $total = $this->getTotalProperty();

        if ($this->cashReceived < $total) {
            Notification::make()
                ->title('Nominal Uang Tunai Kurang')
                ->body('Uang tunai diterima kurang dari total belanja.')
                ->danger()
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Generate Order Number (#POS-YMD-XXXX)
            $orderNumber = '#POS-' . date('ymd') . '-' . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);

            // 2. Find POS channel
            $channelId = DB::table('channels')->where('code', 'CHN-POS')->value('id') ?? 1;

            // 3. Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'outlet_id' => $this->selectedOutletId,
                'channel_id' => $channelId,
                'customer_id' => $this->customerId,
                'cashier_id' => auth()->id(),
                'subtotal' => $total,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $total,
                'payment_status' => 'paid',
                'payment_method' => 'Tunai',
                'fulfillment_status' => 'completed',
            ]);

            // Create payment record
            \App\Models\OrderPayment::create([
                'order_id' => $order->id,
                'payment_method_id' => 1, // Tunai
                'amount' => $total,
                'reference_number' => $orderNumber,
                'paid_at' => now(),
                'status' => 'paid',
            ]);

            // 4. Create Order Items & Decrement Inventory
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_amount' => 0,
                    'total_price' => $item['price'] * $item['quantity'],
                ]);

                // Decrement stock in selected outlet
                $inventory = Inventory::where('product_id', $item['product_id'])
                    ->where('outlet_id', $this->selectedOutletId)
                    ->first();

                if ($inventory) {
                    $inventory->decrement('quantity', $item['quantity']);
                }

                // Increment sales_count pada produk untuk fitur Smart Sorting (Barang Laris Muncul di Depan)
                Product::where('id', $item['product_id'])->increment('sales_count', $item['quantity']);
            }

            // 5. Add Loyalty points if customer exists
            if ($this->customerId) {
                $pointsEarned = (int) floor($total / 10000);
                if ($pointsEarned > 0) {
                    Customer::where('id', $this->customerId)->increment('loyalty_points', $pointsEarned);
                }
            }

            DB::commit();

            // Store summary for receipt modal
            $this->lastOrderSummary = [
                'order_number' => $orderNumber,
                'order_reference' => $orderNumber,
                'date' => now()->format('d M Y, H:i'),
                'outlet_name' => Outlet::find($this->selectedOutletId)?->name ?? 'Muliku Store',
                'cashier_name' => $this->activeShift?->cashier_name ?? auth()->user()->name ?? 'Kasir',
                'items' => $this->cart,
                'total' => $total,
                'total_amount' => $total,
                'cash_received' => $this->cashReceived,
                'change_due' => $this->getChangeDueProperty(),
                'change_amount' => $this->getChangeDueProperty(),
            ];

            $this->showSuccessModal = true;

            Notification::make()
                ->title('Pembayaran Tunai Berhasil!')
                ->body("Transaksi $orderNumber telah dicatat dan stok berkurang otomatis.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Terjadi Kesalahan Transaksi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function startNewTransaction()
    {
        $this->showSuccessModal = false;
        $this->lastOrderSummary = null;
        $this->clearCart();
    }

    public function closeSuccessModal()
    {
        $this->startNewTransaction();
    }
}
