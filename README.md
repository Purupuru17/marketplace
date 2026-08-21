# Laravel Marketplace Master Plan V2

## Riset selesai. Ringkasan arsitektur & hasil konversi.

### Konteks
- Pola datatable **server-side** (`x-idcore::datatable-server` + endpoint ajax +
  `DataTableService`) yang dipakai `UserController` dijadikan acuan untuk seluruh
  index view admin.
- Setiap controller memakai method `ajax(Request)` sendiri
  (`match($type, $source)` -> `tableIndex`), tanpa trait. Nanti bisa ditambah
  source handler lain (mis. `generatePresensi`) di `match` yang sama.
- `DataTableService::process` di-enhance backward-compatible:
  - `searchableColumns` menerima `string|callable` (callable untuk pencarian relasi via `whereHas`).
  - Param opsional `$sortableColumns` sebagai whitelist sort terpisah (sort kolom agregat `withCount`).
  - Kolom relasi tidak di-`sortable` di definisi kolom (whitelist sort hanya kolom DB nyata).

### Hasil konversi (semua admin index pakai datatable-server)
- Core RBAC : `user`, `group`, `hak-akses`, `menu`.
- Master : `store-level`, `customer-level`, `category`, `attribute`, `location-node`, `location-distance`.
- Catalog : `product`, `product-variant`.
- Toko : `store`, `subscription`, `subscription-invoice`, `order`, `promotion`.
- Tidak dikonversi : `wallet` (dashboard kartu/form), halaman customer-facing (Blade), `log` & `setting` (lihat keputusan di bawah).

### Error page & permission
- View `resources/views/errors/403.blade.php` + `404.blade.php` (partial `errors/partials/page.blade.php`,
  gaya TailAdmin) — view di app.
- `CheckCorePermission` : ajax (`expectsJson()`/api) -> JSON `{status, message}` 403;
  request biasa -> `abort(403)` -> view `errors::403`. `PermissionDoesNotExist` ditangkap jadi
  403 (bukan 500).
- `bootstrap/app.php` : `shouldRenderJsonWhen(expectsJson || api/*)` -> 404 ajax juga JSON.
- Seeder: semua modul yang punya endpoint ajax di-tambah permission `*.ajax`
  (CoreDatabaseSeeder sudah, ditambah Master/Location/Catalog/Store + grant role Toko untuk
  product, product-variant, orders, promotion). Wallet & chat tanpa `ajax`.
- `MenuTreeBuilder::markActive` pakai segment boundary
  (`request()->is([url, url.'/*'])`) — `/katalog/product-variant` tidak lagi mengaktifkan
  `/katalog/product`.

### Status
- 197 test passed (669 assertions), pint bersih.
- Route `/` diubah ke `redirect(storefront.index)` — `ExampleTest` disesuaikan.

## Keputusan & Rencana Berikutnya

### 1. Setting & Log Controller di core (masih kosong)
- `SettingController` : index kosong. Keputusan : **TUNDA** (YAGNI) — diisi saat ada
  kebutuhan nyata. Route `sistem.setting.*` ada tanpa menu.
- `LogController` : index kosong. Keputusan : **ISI dengan ACTIVITY LOG BARU**.
  - Tabel `activity_logs` (user / event / description / subject / properties / created_at).
  - Pencatatan event penting : login/logout + audit CRUD di area sistem
    (via middleware/helper tanpa rombak tiap controller).
  - Halaman `sistem/log` pakai pola `datatable-server` (ajax/tableIndex), search
    event/description, sort `created_at`.
  - Tambah permission `log.index` (+ `log.delete` bila hapus log diizinkan), grant admin,
    tambah menu "Log" di `CoreDatabaseSeeder`.

### 2. Customer pindah ke React (SPA terpisah) + API Sanctum
- Mode auth : **SANCTUM TOKEN BEARER** (personal access token). React simpan token.
- Scope : **SEMUA modul customer + storefront** (auth, storefront, cart, checkout,
  order, payment, point, rating, favorite, address, chat).
- Catatan : **Livewire TIDAK pernah terpasang** (klaim Livewire di dokumentasi awal keliru)
  — transisi React tidak perlu membuang kode Livewire.
- Logika dipakai ulang : `app/Services/Customer/*` sudah rapi; API cukup bungkus service.

### Status API Sanctum (implementasi selesai)
- `laravel/sanctum` terinstall + migrasi `personal_access_tokens`.
- Guard baru : `api-customer` (driver `sanctum`, provider `customers`); `Customer` pakai `HasApiTokens`.
- `config/cors.php` : paths `api/*` + `/broadcasting/auth`, origin `FRONTEND_URL` (default `*`, token bearer tanpa credentials).
- `routes/api.php` + `app/Http/Controllers/Api/Customer/*` (envelope `{data}`, validasi `validate()` → 422 JSON):
  - Publik : register, login, storefront (stores / store / product).
  - Auth (`auth:api-customer`) : me, logout (revoke token), addresses (CRUD+default),
    cart (summary/add/update/remove), checkout (summary + placeOrder), orders (index/show),
    payments (info + confirm simulasi), points, ratings, favorites, chats (list/start/show/send).
  - Login manual (bukan session) : email + `Hash::check` + status aktif -> `createToken('react')`.
  - Throttle dipasang di register/login/action/payment.
- Chat Reverb : `BroadcastAuthController` resolver kini `api-customer` -> `customer` -> `web`
  (React kirim `Authorization: Bearer` ke `/broadcasting/auth`).
- Tes API : `tests/Feature/Api/CustomerApiTest.php` (13 test: auth + storefront lengkap + cart).
  Total : 210 test passed (715 assertions).
- **Ditunda** : arsip route + Blade web customer (`customer.*`, storefront, daftar/masuk)
  sampai React live — menghapus sekarang akan memecah ~100 test web yang masih valid.
  Route `/` tetap redirect `storefront.index` (web) selama transisi.

---

## Spec API Customer (acuan Frontend React)

### Konvensi
- Base URL : `APP_URL` + `/api/v1` (mis. `https://app.example/api/v1`). Versi API dikunci di prefix `v1`.
- Envelope sukses : `{ "data": ... }`. Error : `{ "message": "..." }` (+ `errors` saat 422).
- Auth : header `Authorization: Bearer <token>` (dari `POST /api/v1/customer/login` atau register).
- Kode status : `200` ok, `201` created, `401` unauth, `403` bukan milik, `404` not found,
  `422` validasi gagal (data di `errors`), `429` rate limit.
- Rate limit (throttle) : `register` (3/menit/IP), `login` (5/menit email+IP),
  `payment` (10/menit), `action` (30/menit, dipakai cart/checkout/rating/favorite/chat tulis).

### Auth
| Method | Path | Auth | Keterangan |
|---|---|---|---|
| POST | `/api/v1/customer/register` | - | Body `name, email, phone?, password, password_confirmation` → `{data:{customer, token}}` (201) |
| POST | `/api/v1/customer/login` | - | `email, password` → `{data:{customer, token}}` |
| GET | `/api/v1/customer/me` | Y | customer profile sbg `{data:{id,name,email,phone,points,level}}` |
| POST | `/api/v1/customer/logout` | Y | revoke token saat itu |

### Storefront (publik)
| Method | Path | Param | Keterangan |
|---|---|---|---|
| GET | `/api/v1/storefront/stores` | `search?`, `per_page?` | toko aktif. Item: `id,name,slug,code,rate_per_km,min_free_distance_km,products_count,location_node` |
| GET | `/api/v1/storefront/stores/{store}` | `search?` (filter produk) | detail toko + `products:{items,pagination}` |
| GET | `/api/v1/storefront/stores/{store}/products/{product}` | - | detail produk (lihat payload di bawah) |
| GET | `/api/v1/storefront/products` | `search?, category_id?, store_id?, per_page?` | katalog global, paginasi |
| GET | `/api/v1/storefront/categories` | - | `{data:{items:[{id,name,slug}]}}` |

`{store}` = slug toko, `{product}` = slug produk.

Payload produk (list): `id, name, slug, description, status, store:{id,name,slug}, category:{id,name,slug},
variants:[{id,sku,price,stock,weight_grams,status,attributes:[{attribute,value}]}],
promotions:[{id,name,type,value,source,starts_at,ends_at}]`.

Payload produk (detail) = payload list + `rating:{average,count}`, `ratings:[{id,rating,review,customer,created_at}]`.

### Alamat (auth)
| Method | Path | Param | Keterangan |
|---|---|---|---|
| GET | `/api/v1/customer/addresses` | `search?` | `{data:{items,pagination}}` |
| POST | `/api/v1/customer/addresses` | `label?, recipient_name, phone, full_address, location_node_id?, lat?, lng?, is_default?` | 201 |
| PUT | `/api/v1/customer/addresses/{address}` | sama | |
| DELETE | `/api/v1/customer/addresses/{address}` | - | |
| POST | `/api/v1/customer/addresses/{address}/default` | - | jadikan utama |

### Keranjang (auth, wajib login)
| Method | Path | Param | Keterangan |
|---|---|---|---|
| GET | `/api/v1/customer/cart` | - | `{data:{items, by_store:[{store,items,subtotal,discount}], subtotal, discount, total}}` |
| POST | `/api/v1/customer/cart` | `variant_id, qty` | 201 `{data:{message,count,cart_item_id}}` |
| PUT | `/api/v1/customer/cart/{item}` | `qty` | |
| DELETE | `/api/v1/customer/cart/{item}` | - | |

Item cart : `id, qty, unit_price, unit_original_price, unit_discount, variant:{id,sku,stock}, product:{id,name,slug}, store:{id,name}`.

### Checkout (auth)
| Method | Path | Param | Keterangan |
|---|---|---|---|
| GET | `/api/v1/customer/checkout/summary` | `address_id?, stores?` | `{data:{address, by_store:[{store, fulfillment_type, payment_method, subtotal,discount,shipping,total}], subtotal, discount, shipping_total, grand_total, payment_methods, available_points}}` |
| POST | `/api/v1/customer/checkout` | `stores[{fulfillment_type, payment_method}], address_id, points?` | 201 `{data:{id,invoice_no,grand_total,status,orders:[{id,order_no,store,fulfillment_type,total,status}]}}` |

`stores` = map per toko: `{store_id: {fulfillment_type: pickup|delivery, payment_method: ...}}`.
`payment_methods` = `cash | bank_transfer` (lihat `PaymentService::METHODS`).
`address_id` wajib bila ada store `delivery`.

### Pesanan, Pembayaran (auth)
| Method | Path | Keterangan |
|---|---|---|
| GET | `/api/v1/customer/orders` | invoice paginate (orders + items snapshot) |
| GET | `/api/v1/customer/orders/{invoice}` | detail (harus miliknya) |
| GET | `/api/v1/customer/payments/{payment}` | `{data:{invoice, order:{id,order_no,store,status}, payment:{id,provider,payment_method,amount,status,paid_at,payment_proof_path}, payment_proof_url, info, message}}` |
| POST | `/api/v1/customer/payments/{payment}/proof` | multipart `proof` (image, max 2MB); hanya `bank_transfer` pending |

### Poin, Rating, Favorit (auth)
| Method | Path | Param | Keterangan |
|---|---|---|---|
| GET | `/api/v1/customer/points` | - | `{data:{available_points, items, pagination}}` |
| POST | `/api/v1/customer/ratings` | `order_item_id, rating(1-5), review?` | 201; wajib order `completed` + belum dinilai |
| DELETE | `/api/v1/customer/ratings/{rating}` | - | hanya miliknya |
| GET | `/api/v1/customer/favorites` | - | `{data:{items:[{id,name,slug,store}]}}` |
| POST | `/api/v1/customer/favorites/toggle` | `product_id` | `{data:{favorited,message}}` |

### Chat (auth, realtime)
| Method | Path | Param | Keterangan |
|---|---|---|---|
| GET | `/api/v1/customer/chats` | - | konversasi miliknya (store+last_message) |
| POST | `/api/v1/customer/chats/start` | `store_id, product_id?` | 201; konversasi unik [customer, store, product] |
| GET | `/api/v1/customer/chats/{conversation}` | - | messages ascending |
| POST | `/api/v1/customer/chats/{conversation}` | `message` | 201; broadcast ke `private chat.{id}` |

Realtime : event `App\Events\MessageSent` (`broadcastAs: message.sent`), channel `private chat.{id}`.
Auth broadcast : `POST /broadcasting/auth` dengan header `Authorization: Bearer` (guard `api-customer`
didukung di `BroadcastAuthController`).

### Catatan Payment
- Provider masih **simulated** (`bank_transfer`/`e_wallet` = Virtual Account/E-Wallet simulasi, `cod`).
- Integrasi **Midtrans nyata** menyusul; desain API tetap (payable info + confirm), hanya isi provider berubah.

---

## Rencana Skema Project React (Frontend Customer)

### Stack usulan
- Vite + **React 19 + TypeScript**, **Tailwind CSS v4**.
- **React Router v7** (data router) — routing + guard autentikasi.
- **TanStack Query v5** — fetch/sw pada API (`queryKey` per modul, invalidate setelah mutasi).
- **Axios** — instance `/api`, interceptor sisip `Authorization: Bearer`, tangani `401` → logout.
- **Echo (laravel-echo) + pusher-js** — chat realtime `private chat.{id}`;
  authorizer kirim bearer ke `/broadcasting/auth` (`authEndpoint` + `auth.headers.Authorization`).
- State lokal : auth token + customer profile (Zustand atau React context);
  token disimpan `localStorage` (opsional: memory-only untuk keamanan lebih ketat).

### Struktur folder
```
src/
  main.tsx  app/ (router, providers, guards)
  api/      client.ts (axios), endpoints/ (auth, storefront, cart, checkout,
            order, payment, point, rating, favorite, chat)
  features/ auth/ storefront/ product/ cart/ checkout/ order/ payment/
            point/ rating/ favorite/ chat/
  components/ ui/ (atom), layout/ (customer shell + navbar + cart-badge)
  hooks/     (query wrapper per modul + auth state)
  lib/       (types/API models, utils, constants)
  types/     api.d.ts
```

### Logika kunci
- **Auth flow** : register/login → simpan `token` + `customer`; interceptor tambah header;
  guard route redirect `/masuk` bila `401`. `/api/v1/customer/logout` revoke + hapus local.
- **Cart** : menu wajib login; setelah add/update/remove → invalidate `['cart']` +
  refresh badge `count` (bisa via `GET /api/v1/customer/cart`).
- **Checkout** : pilih alamat utama → `GET checkout/summary` → pilih fulfillment + metode **per toko**
  → `POST checkout` dengan `stores: {store_id: {fulfillment_type, payment_method}}`.
- **Pembayaran** : `GET payments/{payment}` perlihatkan `info` (rekening toko) → upload bukti via
  `POST payments/{payment}/proof` (multipart `proof`). Status `paid` hanya di-set toko.
- **Rating** : muncul hanya untuk order item dgn status order `completed` — data order → item,
  submit `POST /ratings` (`order_item_id`), invalidate detail order + product.
- **Poin** : `GET /api/v1/customer/points`; di checkout bisa pilih redeem (kelipatan 100).
- **Chat** : list → buka konversasi (`GET` load history + `POST` kirim) → Echo subscribe
  `chat.{id}` terima `message.sent`, prepend; `markRead` sisi server saat buka.
- **Storefront** : browse via `/storefront/stores`, `/storefront/products` (+ `category_id`), detail `.../products/{slug}`.

### Env (`.env` frontend)
```
VITE_API_URL=https://app.example/api/v1      # base api (prefix versi v1 sudah termasuk)
VITE_REVERB_KEY=..., VITE_REVERB_HOST=..., VITE_REVERB_PORT=443, VITE_REVERB_SCHEME=https
VITE_MIDTRANS_CLIENT_KEY=...                # saat integrasi Midtrans
```

### Catatan
- Backend saat ini **masih menyajikan Blade customer web** sampai React live (arsip Blade ditunda).
- Soal deployment frontend : static host (Vercel/Netlify) → proxy `/api` & `/broadcasting`
  ke backend, atau set `FRONTEND_URL` di backend CORS + konek langsung.
- Saat Midtrans aktif : backend terbitkan Snap token (import client key), React render `window.snap.pay`.


### Perubahan Skema 
1. Saat checkout pilih dahulu Opsi 
    a. Ambil Sendiri -> Berarti customer tidak perlu tentukan alamat pengiriman karena dia datang langsung ke toko
    b. Kirim/Antar -> Tentukan alamat, hitung ongkir dan seterusnya seperti skema saat ini
2. Opsi pembayaran ada 2
    a. Cash -> Pembeli bayar langsung ke penjual. Ntah itu ambil sendiri atau di antar oleh penjual (tidak pakai kurir pihak ketiga)
    b. Transfer bank manual -> Saat checkout dan pilih transfer, muncul rekening bank toko. Setelah itu d detail order, ada form upload bukti pembayaran.
    Kedua jenis pembayaran ini hanya d validasi (LUNAS) oleh toko, customer tidak bisa melunaskan sendiri
Dengan ada nya skema baru ini, sesuaikan untuk migrasi database, controller, view nya hingga k API. Fokus ke tenant toko dahulu, untuk customer karena frontend react terpisah jadi di kerjakan nanti saja

## Perubahan Skema Baru (`2026_08_18` – `2026_08_19`)

- **Checkout per‑toko**: `fulfillment_type` (pickup/delivery) & `payment_method` (cash/bank_transfer) per store, bukan per invoice.
- **Payment per order**: `payments.order_id` FK; `payments.invoice_id` dihapus (redundan); `invoice.payments()` via `hasManyThrough` melalui `orders`; `PaymentService::createPaymentForOrder` per order.
- **Bank snapshot per store**: `stores.bank_name/account_number/account_name` (rekening toko saat checkout); `payments.bank_snapshot` json per order.
- **Upload bukti**: `payments.payment_proof_path` (file ke `public/payment-proofs`); `payment_proof_path` di `Payment` model + `PaymentService::uploadProof`.
- **Customer points dihapus**: kolom `customers.points` dead (balance dari `point_transactions`); API `AuthController` kini pakai `LoyaltyService::availablePoints`.
- **Invoice`points_used` dihapus**: kolom `invoices.points_used` tidak dibuat; `LoyaltyService::redeem` update point hanya via `PointTransaction`.
- **Relasi** `Invoice::payments()` → `hasManyThrough(Payment::class, Order::class, 'invoice_id', 'order_id')`.
- **Payment route**: web & API pindah ke `{payment}` (per‑order), upload proof via `POST payment/{payment}/proof`.
- **Seeder**: `STR-DEMO1` dengan `bank_name='BCA'`, `account_number`, `account_name`.


Session   Sesuaikan tampilan customer dengan template HTML
  Continue  opencode -s ses_fe0c3c321ffenA5oI139xgBQf5