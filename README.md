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
  - Rencana : `composer require laravel/sanctum` -> migrasi `personal_access_tokens` ->
    guard sanctum utk Customer -> `routes/api.php` (publik storefront + authed customer)
    -> Reverb chat pakai bearer -> Blade customer diarsipkan setelah API siap.