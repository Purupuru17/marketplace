Ringkasan Project Marketplace Laravel 13 - Master Plan V2
Dokumen ini merupakan ringkasan hasil diskusi dari awal perancangan hingga penyempurnaan arsitektur (27 poin). Dokumen ini dimaksudkan sebagai konteks awal ketika memulai chat baru.


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