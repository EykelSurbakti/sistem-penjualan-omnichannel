<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;

class KatalogToko extends Component
{
    public $search = '';
    public $selectedCategory = null;

    // Modal Edit Product State
    public $showEditModal = false;
    public $editingProductId = null;
    public $editName = '';
    public $editSku = '';
    public $editPrice = 0;
    public $editStock = 0;

    // Modal Create Product State
    public $showCreateModal = false;
    public $createName = '';
    public $createSku = '';
    public $createCategoryId = null;
    public $createPrice = 0;
    public $createStock = 10;

    public function mount()
    {
        //
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function openEditModal($productId)
    {
        $product = Product::with('inventories')->findOrFail($productId);
        $outletId = auth()->user()->outlet_id ?? 1;
        $inv = $product->inventories->where('outlet_id', $outletId)->first();

        $this->editingProductId = $product->id;
        $this->editName = $product->name;
        $this->editSku = $product->sku;
        $this->editPrice = (float) $product->base_price;
        $this->editStock = $inv ? $inv->quantity : 0;

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingProductId = null;
    }

    public function saveProductEdit()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editSku' => 'required|string|max:50',
            'editPrice' => 'required|numeric|min:0',
            'editStock' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($this->editingProductId);
        $product->update([
            'name' => $this->editName,
            'sku' => $this->editSku,
            'base_price' => $this->editPrice,
        ]);

        $outletId = auth()->user()->outlet_id ?? 1;
        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'outlet_id' => $outletId],
            ['quantity' => $this->editStock]
        );

        $this->closeEditModal();
        session()->flash('pesan_sukses', 'Barang berhasil diperbarui!');
    }

    public function openCreateModal()
    {
        $this->createName = '';
        $this->createSku = 'SKU-' . strtoupper(Str::random(5));
        $this->createCategoryId = null;
        $this->createPrice = 0;
        $this->createStock = 10;
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function saveNewProduct()
    {
        $this->validate([
            'createName' => 'required|string|max:255',
            'createSku' => 'required|string|max:50|unique:products,sku',
            'createPrice' => 'required|numeric|min:0',
            'createStock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $this->createName,
            'sku' => $this->createSku,
            'slug' => Str::slug($this->createName . '-' . Str::random(4)),
            'category_id' => $this->createCategoryId,
            'base_price' => $this->createPrice,
            'is_active' => true,
        ]);

        $outletId = auth()->user()->outlet_id ?? 1;
        Inventory::create([
            'product_id' => $product->id,
            'outlet_id' => $outletId,
            'quantity' => $this->createStock,
        ]);

        $this->closeCreateModal();
        session()->flash('pesan_sukses', 'Barang baru berhasil ditambahkan ke katalog toko!');
    }

    public function render()
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $categories = Category::where('is_active', true)->get();

        $query = Product::with(['category', 'inventories' => function ($q) use ($outletId) {
            $q->where('outlet_id', $outletId);
        }])->where('is_active', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        $products = $query->orderBy('name')->get();

        return view('livewire.katalog-toko', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('components.layouts.app');
    }
}
