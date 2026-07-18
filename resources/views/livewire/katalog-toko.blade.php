<div style="min-height: 100vh; display: flex; flex-direction: column; background: #F1F5F9;">
    
    {{-- HEADER BIRU KONSISTEN MULIKU STORE --}}
    <header style="background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); height: 56px; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; color: #ffffff; box-shadow: 0 4px 12px rgba(21, 101, 192, 0.22); flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ url('/portal-kasir') }}"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; background: rgba(255,255,255,0.18); color: #ffffff; font-weight: 800; font-size: 12px; text-decoration: none; border: 1px solid rgba(255,255,255,0.25);">
                &larr; Kembali ke Portal
            </a>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h1 style="font-size: 15px; font-weight: 900; margin: 0; color: #ffffff;">MULIKU STORE</h1>
                    <span style="background: #34D399; color: #064E3B; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">PENGELOLAAN STOK</span>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ url('/admin/pos-kasir') }}"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; background: #ffffff; color: #1565C0; font-weight: 900; font-size: 12px; text-decoration: none; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                🖥️ Buka Mesin Kasir &rarr;
            </a>
        </div>
    </header>

    {{-- KONTEN UTAMA KATALOG CEPAT --}}
    <main style="flex: 1; padding: 24px; max-width: 1180px; width: 100%; margin: 0 auto;">
        
        {{-- NOTIFIKASI SUKSES --}}
        @if (session()->has('pesan_sukses'))
            <div style="padding: 14px 18px; border-radius: 12px; background: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; font-weight: 800; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <span>✓</span>
                <span>{{ session('pesan_sukses') }}</span>
            </div>
        @endif

        {{-- BAR SEARCH & ACTION --}}
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: #ffffff; padding: 18px 22px; border-radius: 16px; border: 1px solid #CBD5E1; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px;">
            
            <div style="flex: 1; min-width: 260px; max-width: 440px; position: relative;">
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="🔍 Cari nama barang atau kode SKU..."
                    style="width: 100%; padding: 10px 32px 10px 14px; border-radius: 10px; border: 2px solid #E2E8F0; font-size: 13px; font-weight: 600; color: #0F172A; outline: none; transition: 0.15s;"
                />
                @if($search)
                    <button wire:click="$set('search', '')" style="position: absolute; right: 10px; top: 10px; border: none; background: transparent; color: #64748B; font-weight: bold; cursor: pointer;">✕</button>
                @endif
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button
                    wire:click="openCreateModal"
                    style="padding: 10px 18px; border-radius: 10px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); color: #ffffff; font-weight: 800; font-size: 13px; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.25); display: inline-flex; align-items: center; gap: 6px;"
                >
                    + Tambah Barang Baru
                </button>
            </div>

        </div>

        {{-- FILTER KATEGORI --}}
        <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 20px;">
            <button
                wire:click="selectCategory(null)"
                style="padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 800; border: none; cursor: pointer; white-space: nowrap; transition: 0.15s; {{ is_null($selectedCategory) ? 'background: #1976D2; color: #ffffff;' : 'background: #ffffff; color: #334155; border: 1px solid #CBD5E1;' }}"
            >
                Semua Kategori
            </button>
            @foreach($categories as $cat)
                <button
                    wire:click="selectCategory({{ $cat->id }})"
                    style="padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 800; border: none; cursor: pointer; white-space: nowrap; transition: 0.15s; {{ $selectedCategory == $cat->id ? 'background: #1976D2; color: #ffffff;' : 'background: #ffffff; color: #334155; border: 1px solid #CBD5E1;' }}"
                >
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- TABEL / DAFTAR BARANG YANG SUPER CEPAT & MUDAH DIBACA --}}
        <div style="background: #ffffff; border-radius: 16px; border: 1px solid #CBD5E1; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748B;">
                            <th style="padding: 14px 18px;">Kode SKU</th>
                            <th style="padding: 14px 18px;">Nama Barang</th>
                            <th style="padding: 14px 18px;">Kategori</th>
                            <th style="padding: 14px 18px;">Harga Jual Toko</th>
                            <th style="padding: 14px 18px;">Stok Laci Toko</th>
                            <th style="padding: 14px 18px; text-align: right;">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $prod)
                            @php
                                $inv = $prod->inventories->first();
                                $qty = $inv ? $inv->quantity : 0;
                            @endphp
                            <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px; transition: 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 14px 18px; font-weight: 800; color: #64748B;">
                                    {{ $prod->sku }}
                                </td>
                                <td style="padding: 14px 18px; font-weight: 800; color: #0F172A;">
                                    {{ $prod->name }}
                                </td>
                                <td style="padding: 14px 18px; color: #475569;">
                                    <span style="padding: 3px 10px; border-radius: 6px; background: #F1F5F9; font-size: 11px; font-weight: 700;">
                                        {{ $prod->category->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td style="padding: 14px 18px; font-weight: 900; color: #1976D2;">
                                    Rp {{ number_format($prod->base_price, 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px 18px;">
                                    <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; {{ $qty > 0 ? 'background: #D1FAE5; color: #065F46;' : 'background: #FEE2E2; color: #991B1B;' }}">
                                        {{ $qty }} Unit
                                    </span>
                                </td>
                                <td style="padding: 14px 18px; text-align: right;">
                                    <button
                                        wire:click="openEditModal({{ $prod->id }})"
                                        style="padding: 6px 14px; border-radius: 8px; background: #EFF6FF; color: #1976D2; border: 1px solid #BFDBFE; font-weight: 800; font-size: 12px; cursor: pointer;"
                                    >
                                        ✏️ Edit Stok/Harga
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 36px; text-align: center; color: #94A3B8;">
                                    Barang belum ditemukan. Klik tombol <b>+ Tambah Barang Baru</b> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    {{-- MODAL EDIT PRODUK CEPAT --}}
    @if($showEditModal)
        <div style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
            <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 460px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);">
                <div style="background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); padding: 20px; color: #ffffff;">
                    <h3 style="font-size: 17px; font-weight: 900; margin: 0;">Edit Barang & Stok Toko</h3>
                    <p style="font-size: 12px; opacity: 0.9; margin-top: 3px;">Perbarui harga atau stok fisik cabang secara cepat</p>
                </div>
                <form wire:submit.prevent="saveProductEdit" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Nama Barang</label>
                        <input type="text" wire:model="editName" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Kode SKU</label>
                        <input type="text" wire:model="editSku" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Harga Jual (Rp)</label>
                            <input type="number" wire:model="editPrice" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Stok Fisik Toko</label>
                            <input type="number" wire:model="editStock" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                        </div>
                    </div>
                    <div style="padding-top: 10px; border-top: 1px solid #E2E8F0; display: flex; gap: 10px;">
                        <button type="button" wire:click="closeEditModal" style="flex: 1; padding: 12px; border-radius: 10px; background: #E2E8F0; color: #334155; font-weight: 800; border: none; cursor: pointer;">Batal</button>
                        <button type="submit" style="flex: 2; padding: 12px; border-radius: 10px; background: #1976D2; color: #ffffff; font-weight: 900; border: none; cursor: pointer;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL TAMBAH PRODUK CEPAT --}}
    @if($showCreateModal)
        <div style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
            <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 460px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);">
                <div style="background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); padding: 20px; color: #ffffff;">
                    <h3 style="font-size: 17px; font-weight: 900; margin: 0;">Tambah Barang Baru Ke Toko</h3>
                    <p style="font-size: 12px; opacity: 0.9; margin-top: 3px;">Tambahkan barang baru langsung tanpa loading lama</p>
                </div>
                <form wire:submit.prevent="saveNewProduct" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Nama Barang</label>
                        <input type="text" wire:model="createName" placeholder="Contoh: Plastik Klip Ukuran 5x8" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Kode SKU</label>
                            <input type="text" wire:model="createSku" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Kategori</label>
                            <select wire:model="createCategoryId" style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;">
                                <option value="">Umum</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Harga Jual (Rp)</label>
                            <input type="number" wire:model="createPrice" placeholder="0" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 4px;">Stok Awal Toko</label>
                            <input type="number" wire:model="createStock" placeholder="10" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #CBD5E1; font-weight: 800; font-size: 13px;" />
                        </div>
                    </div>
                    <div style="padding-top: 10px; border-top: 1px solid #E2E8F0; display: flex; gap: 10px;">
                        <button type="button" wire:click="closeCreateModal" style="flex: 1; padding: 12px; border-radius: 10px; background: #E2E8F0; color: #334155; font-weight: 800; border: none; cursor: pointer;">Batal</button>
                        <button type="submit" style="flex: 2; padding: 12px; border-radius: 10px; background: #1976D2; color: #ffffff; font-weight: 900; border: none; cursor: pointer;">+ Simpan Barang Baru</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
