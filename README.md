# Sistem Manajemen Penjualan Omnichannel (Laravel 11 + Filament v3 + MariaDB)

Sistem Retail Management berstandar **iSeller Commerce Dashboard** yang mendukung pengelolaan multi-channel (POS & Toko Online), inventaris multi-outlet, pesanan terpusat, pelanggan & loyalti, serta laporan analitik terpadu.

---

## 🚀 Arsitektur & Teknologi

- **Backend Framework**: Laravel 11 (PHP 8.3)
- **Admin & Dashboard Panel**: Filament v3 (`^3.2`) dengan UI/UX modern (collapsible sidebar, Skema warna Biru `#2563EB`, indikator tren, Empty State panduan)
- **Database**: MariaDB (`omnichannel_retail`)
- **Role & Permission**: Spatie Laravel Permission (`^8.3`)

---

## 📂 Struktur Modul & Database Inti

1. **Modul Multi-Outlet & Pengguna**
   - `outlets` — Daftar cabang fisik & gudang (`OUT-JKT-01`)
   - `shifts` & `shift_user` — Pengelolaan shift kerja kasir & kas awal/akhir
   - `users`, `roles`, `permissions` — Hak akses berbasis role (`Super Admin`, `Manager Outlet`, `Kasir POS`, `Staff Gudang`)

2. **Modul Produk & Katalog**
   - `categories` — Kategori hirarkis
   - `products` & `product_variants` — Mendukung produk tunggal maupun varian warna/ukuran, strategi stok habis (`stop` / `continue` backorder)
   - `collections` & `collection_product` — Koleksi promosi produk
   - `inventories` — Stok multi-outlet & ambang batas low stock
   - `stock_transfers` & `stock_transfer_items` — Mutasi stok antar outlet

3. **Modul Pesanan (Order Management)**
   - `orders` — Pesanan omnichannel (`channel_id` membedakan POS, Web, Marketplace)
   - `order_items` — Rincian item pesanan
   - Status ganda terpisah: `payment_status` (`unpaid`, `paid`, `refunded`) dan `fulfillment_status` (`processing`, `ready`, `shipped`, `completed`, `cancelled`)

4. **Modul Pelanggan & Marketing**
   - `customer_groups` & `customers` — Data pelanggan & segmentasi VIP
   - `channels` — Daftar channel penjualan
   - `discounts` & `promotions` — Aturan diskon persentase maupun nominal tetap
   - `price_books` & `price_book_items` — Harga khusus per grup pelanggan/channel
   - `loyalty_transactions` — Pengumpulan & penukaran poin loyalti

5. **Modul Pembayaran & Payout**
   - `payment_methods` & `order_payments` — Tunai, QRIS, Kartu Debit/Kredit, Transfer Bank
   - `payout_settings` & `payouts` — Jadwal pencairan saldo penjualan ke rekening bank

---

## 🔑 Akun Default (Seeder)

Setelah migrasi dan seed (`php artisan migrate:fresh --seed`), gunakan akun berikut untuk masuk ke dashboard:

- **URL Dashboard Admin**: `http://127.0.0.1:8000/admin`
- **Super Admin**:
  - Email: `admin@iseller.local`
  - Password: `password`
- **Kasir POS**:
  - Email: `kasir@iseller.local`
  - Password: `password`

---

## 🛠 Cara Menjalankan Project

Jalankan perintah PowerShell dari direktori project:
```powershell
# Jalankan script praktis (otomatis mengecek MariaDB dan memulai server Laravel)
.\start-project.ps1
```
Atau secara manual:
```powershell
php artisan serve
```
