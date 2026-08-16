# Laravel Marketplace Master Plan V2

## Riset selesai. Berikut hasil riset dan rencana lengkap.

### Konteks
- Pengguna memiliki **UserController + index view** yang sudah memakai pola
  datatable **server-side** (`datatable-server` + ajax endpoint + `DataTableService`),
  dengan filter slot, search, sort, pagination, dan action per baris
  (`dt-actions`, detail/edit/delete yang digate permission). Pola ini dinilai
  "baik" dan diminta diterapkan ke seluruh index view lain.
- Scope yang disepakati: **core RBAC (sistem) + modul app admin**.
- Konversi mengikuti pola UserController sebagai acuan.

### Target konversi (server-side `datatable-server`)
| Kelompok | Modul |
|---|---|
| Core RBAC (Fase 2) | `group`, `hak-akses`, `menu` |
| Master (Fase 3) | `store-level`, `customer-level`, `category`, `attribute`, `location-node`, `location-distance` |
| Catalog (Fase 3) | `product`, `product-variant` |
| Toko (Fase 3) | `store`, `subscription`, `subscription-invoice`, `order`, `promotion` |

### Tidak dikonversi (bukan tabel index admin)
- `toko.wallet` — halaman dashboard: kartu saldo + form penarikan + 2 tabel
  kecil (limit 50, aksi inline approve/reject). Bukan CRUD index.
- Halaman customer-facing (`customer.address`, `customer.order`, `customer.point`) —
  layout card, bukan tabel admin.
- `sistem.log`, `sistem.setting` — placeholder (index kosong).

### Keputusan arsitektur
- **Fase 1.1** — `DataTableService::process` di-enhance backward-compatible:
  - `searchableColumns` menerima `string|callable` (callable dipakai untuk
    pencarian relasi via `whereHas`).
  - Param opsional `$sortableColumns` sebagai whitelist sort terpisah
    (default: entry string dari `searchableColumns`). Memungkinkan sort kolom
    agregat seperti `permissions_count` dari `withCount`.
- **Fase 1.2** — Trait baru `HasAjaxDatatable` di core
  (`IdCore\CoreStarter\Http\Controllers\Concerns`) berisi method `ajax(Request)`
  yang dispatch `match(type, source)` ke `tableIndex(Request)`. Setiap controller
  cukup mengimplementasikan `tableIndex()`. Menghilangkan boilerplate `ajax()`
  yang duplikat di tiap controller.
- **Fase 1.3 — SKIP (sesuai instruksi)** — config `permission_map` tetap
  `'ajax' => 'ajax'`. Seeder permission **tidak disentuh**. Grant permission
  `{resource}.ajax` ke role diatur **manual oleh pengguna**. Konsekuensi:
  selama permission belum dibuat/digrant, endpoint ajax akan 403.

### Temuan penting
- `permission_map` config `idcore.php` memetakan method controller ke suffix
  permission; `BaseCoreController::middleware()` menerapkan `core_permission`
  otomatis untuk route yang menunjuk method controller. Jadi route ajax
  (`Route::get('X/ajax', [XController::class, 'ajax'])`) otomatis butuh
  permission `{resource}.ajax`.
- `CheckCorePermission` → `$role->hasPermissionTo($permission)`: permission
  yang tidak ada di DB menghasilkan 403 untuk semua role (termasuk admin).
- Kolom sort server-side hanya boleh kolom DB nyata (whitelist di
  `DataTableService`). Kolom relasi ditandai `sortable: false` di definisi
  kolom, atau di-handle via `sortableColumns`.
- Formatter wajib menyertakan `name_plain` (atau `name`) — dipakai `dt-actions`
  untuk pesan konfirmasi hapus.
- `MenuController` index memakai tree yang di-flatten. `tableIndex` untuk menu
  memproses array hasil flatten secara manual (search global + pagination
  slice), karena struktur tree tidak bisa jadi query flat DB.

### Tes yang terdampak (harus disesuaikan saat modul dikonversi)
- `CatalogSmokeTest` (assert row `L · Merah`, tenant scoping produk) → pindah ke
  assertion endpoint ajax.
- `StoreOrderSmokeTest` (assert `order_no` di index) → pindah ke ajax.
- `PromotionSmokeTest` (`Promo Agustus` di index) → pindah ke ajax.
- `DashboardComponentReferenceTest` tetap lolos: `datatable-server` memiliki
  markup yang sama (`x-data`, `dt-per-page`, `Showing`, `Search`).

### Status
- Fase 1 (infra) + Fase 2 (core RBAC: group, hak-akses, menu) selesai.
- Fase 3 (master, catalog, toko) menyusul.
