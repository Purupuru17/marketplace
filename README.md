Kita fokus perbaiki terkait datatable. Saya mau hanya ada 2 pemanggilan data pada datatable
1. Server side ajax (pagination, search, sort semua live ajax)
2. Pakai ajax juga tp hanya saat awal reload sj, json d ambil lalu selanjutnya client side
3. Jadi method index pada controller tidak perlu throw data
4. Components datatabel terlalu banyak passing d x-data, apakah bisa lebih simple
5. Kita ujicoba dulu pada 
   - core-starter/resources/views/sistem/user/index.blade.php
   - core-starter/src/Http/Controllers/Sistem/UserController.php
Saya sdh coba edit controller nya, mirip sprt itu