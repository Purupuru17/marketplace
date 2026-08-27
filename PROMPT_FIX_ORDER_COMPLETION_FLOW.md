# Prompt: Perbaiki Alur Status Pesanan (Order Completion Flow)

## Konteks

Repo: `Purupuru17/marketplace` (Laravel 13, branch `master`).

Alur bisnis nyata yang harus didukung sistem (COD lokal, bukan e-commerce
prepaid biasa):

1. Pesanan dibuat → status `pending`, pembayaran `pending` (belum lunas).
2. **Ambil sendiri**: toko ubah `pending → processing → ready` (siap
   diambil). Customer datang, bayar cash/transfer sekaligus ambil barang.
   Toko validasi pembayaran (`paid`). **Customer** yang menandai pesanan
   `completed` (barang sudah diterima) — bukan toko.
3. **Diantar**: toko ubah `pending → processing → ready` (dalam
   pengantaran). Kurir toko datang ke alamat, customer bayar cash/transfer
   sekaligus terima barang. Toko validasi pembayaran (`paid`). **Customer**
   yang menandai pesanan `completed`.

## Bug yang harus diperbaiki

Backend saat ini didesain untuk skema "bayar dulu baru diproses" (prepaid),
bukan "proses dulu, bayar & terima barang belakangan" (COD). Akibatnya:

1. **Tidak ada endpoint bagi customer untuk menandai pesanan selesai.**
   `app/Http/Controllers/Api/Customer/OrderController.php` cuma punya
   `index` dan `show`. Transisi ke `completed` cuma bisa lewat
   `StoreOrderService::transition()` (toko saja).
2. **Status enum `shipped` generik untuk pickup & delivery**, padahal
   maknanya beda ("Shipped" salah kaprah untuk pesanan pickup).
3. **Tidak ada guard yang mewajibkan payment `paid` sebelum order boleh
   `completed`.**
4. Kalau tidak diperbaiki, pesanan cash yang sudah lunas & diterima bisa
   nyangkut selamanya di status `shipped`/`ready` karena tidak ada mekanisme
   penutupnya dari sisi customer.

## Rencana Eksekusi

### 1. Migration — rename enum value `shipped` → `ready`

Buat migration baru (pakai timestamp setelah migration terakhir yang ada,
cek `database/migrations/` untuk urutan terbaru):

```php
public function up(): void
{
    DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','ready','completed','cancelled') DEFAULT 'pending'");
    DB::table('orders')->where('status', 'shipped')->update(['status' => 'ready']);
}

public function down(): void
{
    DB::table('orders')->where('status', 'ready')->update(['status' => 'shipped']);
    DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','shipped','completed','cancelled') DEFAULT 'pending'");
}
```

Cari **semua** referensi string `'shipped'` di codebase (`grep -rn "shipped"
app/ resources/ tests/ routes/`) dan ganti ke `'ready'` — termasuk di Blade
views, filter status di panel toko, dan test yang meng-assert status literal
`shipped`.

### 2. `app/Services/Store/StoreOrderService.php`

Update `TRANSITIONS` — toko **tidak boleh lagi** transisi langsung ke
`completed`:

```php
public const TRANSITIONS = [
    'pending' => ['processing', 'cancelled'],
    'processing' => ['ready', 'cancelled'],
    'ready' => ['cancelled'], // toko masih bisa cancel kalau customer tidak jadi ambil/terima
    'completed' => [],
    'cancelled' => [],
];

public const STATUS_LABELS = [
    'pending' => 'Menunggu Diproses',
    'processing' => 'Sedang Disiapkan',
    'ready' => 'Siap Diserahkan', // label default; label fulfillment-aware ada di helper terpisah, lihat langkah 4
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];
```

Di method `transition()`, **hapus** blok berikut (logic completed dipindah
ke customer flow di langkah 3):
```php
if ($to === 'completed') {
    $this->walletService->settle($order);
    $this->loyaltyService->creditEarn($order);
}
```

### 3. Endpoint baru: customer konfirmasi terima pesanan

Buat service baru `app/Services/Customer/CustomerOrderService.php`:

```php
<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\Store\StoreWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerOrderService
{
    public function __construct(
        protected StoreWalletService $walletService,
        protected LoyaltyService $loyaltyService
    ) {}

    public function confirmReceived(Customer $customer, Order $order): Order
    {
        abort_unless($order->invoice->customer_id === $customer->id, 403);

        if ($order->status !== 'ready') {
            throw ValidationException::withMessages([
                'status' => 'Pesanan belum siap diserahterimakan.',
            ]);
        }

        $order->loadMissing('payments');
        $unpaid = $order->payments->isEmpty() || $order->payments->contains(fn ($p) => $p->status !== 'paid');

        if ($unpaid) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran belum dikonfirmasi toko. Selesaikan pembayaran terlebih dahulu.',
            ]);
        }

        return DB::transaction(function () use ($order, $customer) {
            $order->update(['status' => 'completed']);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => 'ready',
                'status_to' => 'completed',
                'changed_by_type' => 'customer',
                'changed_by_id' => $customer->id,
                'notes' => 'Pesanan dikonfirmasi diterima oleh customer.',
            ]);

            $this->walletService->settle($order);
            $this->loyaltyService->creditEarn($order);

            return $order->refresh()->load(['payments', 'items', 'store']);
        });
    }
}
```

Sesuaikan namespace/import `LoyaltyService` (cek lokasi sebenarnya di
`app/Services/Customer/LoyaltyService.php`, sudah dipakai di
`StoreOrderService` — pastikan konsisten). Registrasikan constructor
dependency ini kalau perlu binding tambahan di service provider (biasanya
tidak perlu, Laravel auto-resolve).

Tambah method di `app/Http/Controllers/Api/Customer/OrderController.php`:

```php
public function __construct(protected \App\Services\Customer\CustomerOrderService $customerOrderService) {}

public function confirmReceived(Request $request, Order $order)
{
    $order = $this->customerOrderService->confirmReceived($request->user('api-customer'), $order);

    return response()->json(['data' => $this->orderPayload($order)]);
}
```

Tambah route di `routes/api.php`, satu grup dengan route customer order
lain (`auth:api-customer` + `active`), pakai `throttle:action`:

```php
Route::post('customer/orders/{order}/confirm-received', [OrderController::class, 'confirmReceived'])
    ->middleware('throttle:action');
```

### 4. Payload API — tambah field turunan biar React tidak duplikasi logic

Di `OrderController::orderPayload()` (dan `payload()` untuk list invoice,
tambahkan hal yang sama di level tiap order dalam invoice), tambahkan:

```php
'status_label' => $this->statusLabel($order),
'can_confirm_received' => $order->status === 'ready'
    && $order->payments->isNotEmpty()
    && $order->payments->every(fn ($p) => $p->status === 'paid'),
```

Buat helper `statusLabel(Order $order): string` di controller yang sama
(atau service bersama supaya bisa dipakai juga oleh Blade & panel toko):

```php
protected function statusLabel(Order $order): string
{
    return match ($order->status) {
        'ready' => $order->fulfillment_type === 'pickup' ? 'Siap Diambil' : 'Sedang Diantar',
        default => \App\Services\Store\StoreOrderService::STATUS_LABELS[$order->status] ?? $order->status,
    };
}
```

Pastikan `payments` selalu di-load sebelum method ini dipanggil (sudah ada
`->load([...'payments'...])` di `show()` — cek juga `index()` sudah
eager-load `orders.payments`, itu sudah benar berdasarkan kode saat ini).

### 5. Update test

- Cari semua test yang assert literal `'shipped'` (`grep -rln "shipped"
  tests/`) dan ganti jadi `'ready'`.
- Tambah `tests/Feature/Api/CustomerOrderConfirmReceivedTest.php` dengan
  skenario:
  - Customer berhasil confirm-received saat status `ready` + payment `paid`
    → order jadi `completed`, wallet toko ke-settle, ada
    `OrderStatusHistory` dengan `changed_by_type = customer`.
  - Gagal (422) kalau status masih `pending`/`processing`.
  - Gagal (422) kalau payment belum `paid`.
  - Gagal (403) kalau order bukan milik customer yang login.
- Update test di `StoreOrderService`/`Store\OrdersController` yang
  sebelumnya mengetes toko bisa transisi `shipped`/`ready → completed` —
  ubah expectation jadi `ValidationException` (transisi tidak diizinkan
  lagi).

### 6. (Opsional, sangat disarankan) Jaring pengaman auto-complete

Customer sering lupa buka app buat konfirmasi meski barang sudah diterima.
Buat scheduled command `app/Console/Commands/AutoCompleteReadyOrders.php`:
order dengan status `ready`, semua payment `paid`, dan `updated_at` lebih
dari 3 hari yang lalu → otomatis `completed` (pakai logic yang sama dengan
`CustomerOrderService::confirmReceived()`, tapi `changed_by_type = 'system'`
dan `changed_by_id = null`, notes: "Dikonfirmasi otomatis oleh sistem").
Daftarkan di scheduler (`routes/console.php`) untuk jalan harian.

## Batasan

- Jangan ubah struktur tabel `orders` selain kolom `status` (enum value
  saja, bukan kolom baru).
- Jangan sentuh logic `StoreWalletService` (`hold`/`settle`/`reverseHold`)
  — logic-nya sudah benar untuk kedua skema (prepaid & COD), cukup ubah
  **siapa yang memicunya**.
- Pastikan tidak ada endpoint/route lama yang rusak — jalankan
  `php artisan route:list` setelah selesai untuk memastikan tidak ada
  duplikasi/konflik route.
- Setelah selesai, jalankan test suite penuh (`php artisan test`) dan
  pastikan semua hijau sebelum dianggap selesai.
