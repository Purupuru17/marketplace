## About
Ringkasan Project Marketplace Laravel 13 - Master Plan V2
Dokumen ini merupakan ringkasan hasil diskusi dari awal perancangan hingga penyempurnaan arsitektur (27 poin). Dokumen ini dimaksudkan sebagai konteks awal ketika memulai chat baru.

## Referensi Template Dashboard Admin
TailAdmin : https://demo.tailadmin.com/
1. Profile : https://demo.tailadmin.com/profile
    - Selesai (minimal tanpa migrasi): info card, Personal Information, Change Password, logout all devices, hapus akun (super admin dilindungi)
2. Form Elements : https://demo.tailadmin.com/form-elements
    - Default Input (selesai: props icon/state/success-message/disabled, padding otomatis)
    - Select (selesai: fix icon arrow-down menumpuk di width sempit)
    - Input Group (selesai: komponen `input-group` dengan left-icon/left-text/left-options/right-icon/right-button-text)
    - Input States (selesai: error otomatis dari `@error`, success + success-message, disabled)
3. Data tables : https://demo.tailadmin.com/data-tables
    - Icon button kolom Action (selesai: partial `dt-actions` gaya TailAdmin, rounded-lg tanpa border, hover subtle edit=brand / delete=error)


1. Ringkasan Project
Membangun Marketplace Multi-Toko berbasis Laravel 13, Blade, Tailwind CSS dan Alpine.js.
Sistem mendukung Admin, Toko dan Customer dengan autentikasi terpisah. Marketplace
mendukung checkout multi-toko, perhitungan ongkir menggunakan graph Dijkstra, pembayaran
(COD, Transfer Manual, Midtrans), wallet toko, subscription SaaS, promo, rating, favorit,
chat, serta arsitektur service yang modular.

2. Teknologi
- Laravel 13
- Blade + Tailwind + Alpine.js
- Livewire (khusus customer: cart & checkout)
- Midtrans
- Laravel Reverb
- RBAC dari idcore/core-starter
- Heroicons
- Service Layer

3. Roadmap
    1. 1. Foundation (Auth, RBAC, Guard, Policy, Store Context)
    2. 2. Master Data
    3. 3. Node Lokasi & Dijkstra
    4. 4. Toko & Subscription
    5. 5. Katalog Produk & Varian
    6. 6. Cart
    7. 7. Checkout
    8. 8. Order Management
    9. 9. Payment
    10. 10. Wallet & Withdrawal
    11. 11. Promotion & Loyalty
    12. 12. Rating & Favorit
    13. 13. Chat
    14. 14. Hardening, Testing & Deployment

4. Ringkasan 27 Arsitektur Inti
    15. 1. Identity & Access (users dan customers dipisah).
    16. 2. Toko sebagai tenant marketplace.
    17. 3. Jam operasional per hari.
    18. 4. Kategori produk bertingkat.
    19. 5. Produk dipisah dari varian.
    20. 6. Atribut dan nilai atribut global.
    21. 7. Inventory menggunakan stock movements.
    22. 8. Cart menggunakan carts + cart_items.
    23. 9. Transaksi sebagai invoice induk.
    24. 10. Order dipisah per toko.
    25. 11. Order item menggunakan snapshot penuh.
    26. 12. Riwayat perubahan status order.
    27. 13. Payment dipisah dari transaksi.
    28. 14. Webhook payment dicatat.
    29. 15. Wallet toko terpisah.
    30. 16. Ledger keuangan immutable.
    31. 17. Withdrawal request.
    32. 18. Level toko.
    33. 19. Subscription dan invoice dipisah.
    34. 20. Node lokasi.
    35. 21. Graph jarak.
    36. 22. Alamat customer dengan snapshot.
    37. 23. Promotion & pricing service.
    38. 24. Point loyalty menggunakan ledger.
    39. 25. Rating berbasis order selesai.
    40. 26. Favorit produk.
    41. 27. Chat real-time setelah core selesai.
5. Prinsip Pengembangan
- Database dirancang terlebih dahulu sebelum coding.
- Business logic ditempatkan pada Service Layer.
- Checkout menggunakan DB Transaction.
- Setiap transaksi memiliki snapshot historis.
- Semua perubahan saldo melalui Wallet Service dan Ledger.
- Security menggunakan Guard + Policy + Tenant Context.
- Pengembangan dilakukan bertahap sesuai sprint.
6. Catatan
Dokumen ini merupakan ringkasan diskusi dan akan digunakan sebagai konteks awal pada chat baru. Dokumen ini menjadi acuan utama sebelum implementasi migration maupun coding dimulai.

7. Progress
- Step 1-13 Selesai: Foundation sampai Chat real-time (Reverb/Pusher). Chat: ChatConversation/ChatMessage, ChatService (start unik per [customer,store,product], send transaction + broadcast resilient), MessageSent ke channel private `chat.{id}`, ChatChannel::join() multi-guard, controller + view customer & store, permission `chat.*`, menu Toko.
- Step 14 (Hardening & Testing) Selesai:
  - A1 Rate limiting: limiter login (5/menit email+IP), register (3/menit IP), payment (10/menit), action (30/menit) di AppServiceProvider; throttle dipasang di route customer sensitif, daftar, masuk, dan POST /login idcore.
  - A2 SecurityHeaders middleware (X-Content-Type-Options, X-Frame-Options SAMEORIGIN, Referrer-Policy, Permissions-Policy, HSTS) di grup web.
  - A3 trustProxies at('*') + URL::forceScheme('https') saat production.
  - A4 SESSION_SECURE_COOKIE=false di .env.example.
  - B1 Refactor seed test: trait Tests\Concerns\SeedsMarketplace (seedCore/Master/Locations/Stores/Catalog/Customers + flushTestCache); 14 file Feature memakainya.
  - B2 Unit test service layer: PromotionServiceTest, LoyaltyServiceTest, StoreWalletServiceTest, CartServiceTest, ChatServiceTest.
  - B3 Test validasi & authz negatif: ValidationSmokeTest, AuthorizationSmokeTest (403 lintas tenant, transisi status ilegal, rating order belum selesai/milik orang lain, penarikan non-admin).
  - Fix hardening: `points_used` ditambahkan ke Invoice::$fillable + cast integer; CheckoutService tidak lagi set points_used saat create (redeem adalah satu-satunya penulis) sehingga terhindar dari double counting.
  - Total: 186 test passed (616 assertions), pint bersih. CI/Docker deployment menyusul.
- Step 15 (Komponen UI Lanjutan + Demo Pages) Selesai:
  - Komponen baru di packages/idcore/core-starter/resources/views/components: datatable (client-side: search/sort/pagination/HTML cell), datatable-server (server-driven via DataTableService), field, form-section, auth-layout, metric-card, notification-dropdown, progress, empty-state, page-header, toolbar, status-badge.
  - Halaman demo + route: GET /demo/datatables (client-side + server-driven), /demo/datatables/roles|permissions (JSON via DataTableService), /demo/form-layout, /signup (guest, auth-layout). DemoController auth-only, grup "Demo" statis di sidebar.
  - Dokumentasi component table di SETUP.md + SUMMARY.md diperbarui.
- Step 15b (Refactor Index ke Client-side DataTable + Demo pindah ke Dashboard) Selesai:
  - Semua index view sistem (User, Group, Hak Akses, Menu) dikonversi dari pagination server-side ke `x-idcore::datatable` client-side (rows di-flatten dari controller, menu tree di-flatten dengan indentasi). Controller kirim `columns` + `rows` (dengan `edit_url`/`delete_url`/`name`).
  - Komponen datatable & datatable-server dapat `actions` slot + prop `actionsHeader`; partial baru `x-idcore::partials.dt-actions` (edit/hapus per baris, `$confirm` + hidden form per-row, id unik via `row.id`).
  - Demo dipindah ke dashboard sebagai Component Reference: seksi DataTables (client-side + server-driven) dan Form Section & Field. Server-driven source: `GET /dashboard/roles-json` → `RoleTableController` (auth-only, tanpa core_permission, via DataTableService).
  - `DemoController`, views `pages/demo/*`, `auth/signup`, route `demo.*` + `signup`, grup statis "Demo" di sidebar, dan DemoPagesSmokeTest dihapus; diganti `DashboardComponentReferenceTest` (4 test).
  - Fix pre-existing: route rusak `sistem.group.ajax` (GroupController tak punya method ajax) dihapus & diganti endpoint dashboard yang proper.
  - Total: 190 test passed (632 assertions), pint bersih (termasuk file yang sebelumnya pre-existing issue), Vite build OK.
- Step 15c (Polish UI TailAdmin) Selesai:
  - Halaman Profile (tanpa migrasi): ProfileController (edit/update/updatePassword/logoutAllDevices/destroy) + route `profile.*` + view `auth/profile.blade.php` (info card, Personal Information `#personal-info`, Change Password, Danger Zone dengan `$confirm`; akun super admin `super@gmail.com` tidak bisa dihapus). Link header diarahkan ke `route('profile')`.
  - Form Elements: `input.blade.php` di-rewrite (prop `icon` Heroicon, `state` error/success + `successMessage`, padding otomatis pl-10/pr-10), `select.blade.php` fix arrow menumpuk (`pl-4 pr-10`, chevron `right-3`), komponen baru `input-group.blade.php` (leftIcon/leftText/leftOptions format `nilai=label|nilai=label`/rightIcon/rightButtonText).
  - Dashboard Component Reference ditambah seksi Input States (`#ref-input-states`) dan Input Group (`#ref-input-group`); seksi input update pakai `state="error"` + demo.
  - Icon button kolom Action `partials/dt-actions.blade.php` di-rewrite gaya TailAdmin: rounded-lg tanpa border, icon svg saja, hover subtle edit=brand / delete=error (tetap edit_url/delete_url + `$confirm`).
  - Tests: DashboardComponentReferenceTest +7 test profile (render, auth guard, update profil, email duplikat, password salah, hapus super admin diblok). Total: 197 test passed (656 assertions), pint bersih, Vite build OK.