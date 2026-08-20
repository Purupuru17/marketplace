<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\StoreLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureStorageLink();
        $this->seedCategories();
        $this->seedStoresAndProducts();
        $this->seedOrdersAndRatings();
    }

    protected function ensureStorageLink(): void
    {
        if (! is_dir(public_path('storage'))) {
            $this->command->call('storage:link');
        }
    }

    protected function generatePlaceholder(string $path, int $width = 400, int $height = 400, string $text = ''): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
        imagefill($image, 0, 0, $bg);
        $color = imagecolorallocate($image, 255, 255, 255);
        if ($text) {
            imagestring($image, 5, 10, $height / 2 - 10, $text, $color);
        }
        ob_start();
        imagejpeg($image);
        $data = ob_get_clean();
        imagedestroy($image);
        Storage::disk('public')->put($path, $data);
    }

    protected function seedCategories(): void
    {
        $categories = [
            'Elektronik', 'Fashion Pria', 'Fashion Wanita', 'Sepatu', 'Tas & Aksesoris',
            'Makanan & Minuman', 'Kesehatan', 'Kecantikan', 'Perawatan Tubuh', 'Rumah Tangga',
            'Perabotan', 'Alat Dapur', 'Otomotif', 'Handphone & Tablet', 'Komputer & Laptop',
            'Kamera & Video', 'Olahraga', 'Mainan & Hobi', 'Buku & Alat Tulis', 'Petshop',
        ];

        foreach ($categories as $idx => $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $idx + 1, 'status' => 'active']
            );
        }
    }

    protected function seedStoresAndProducts(): void
    {
        $freeLevel = StoreLevel::where('name', 'Free')->first() ?? StoreLevel::first();
        $location = LocationNode::first() ?? LocationNode::firstOrCreate(['name' => 'Jakarta']);

        $storeNames = [
            'Toko Berkah', 'Mandiri Jaya', 'Sumber Rezeki', 'Bintang Utama', 'Cahaya Abadi',
            'Mitra Sejati', 'Karya Mandiri', 'Putra Jaya', 'Berkah Bersama', 'Sinar Abadi',
        ];

        $productNames = [
            ['Laptop Gaming', 12000000, 1500],
            ['Smartphone Pro', 8000000, 200],
            ['Headphone Wireless', 500000, 200],
            ['Power Bank 20000mAh', 350000, 400],
            ['Charger Fast 65W', 250000, 100],
            ['Kaos Polos', 150000, 200],
            ['Kemeja Formal', 350000, 300],
            ['Celana Jeans', 400000, 500],
            ['Jaket Hoodie', 300000, 600],
            ['Sepatu Sneakers', 600000, 800],
            ['Tas Ransel', 450000, 400],
            ['Jam Tangan', 750000, 100],
            ['Kipas Angin', 300000, 2000],
            ['Blender', 450000, 1500],
            ['Rice Cooker', 600000, 2000],
            ['Set Panci', 500000, 2500],
            ['Kopi Bubuk', 80000, 200],
            ['Teh Celup', 45000, 150],
            ['Susu Kental Manis', 25000, 100],
            ['Mie Instan', 35000, 80],
            ['Vitamin C', 120000, 50],
            ['Sunscreen SPF50', 180000, 60],
            ['Sabun Wajah', 85000, 100],
            ['Pelembab', 150000, 80],
            ['Parfum', 250000, 50],
        ];

        for ($i = 0; $i < 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'toko'.($i + 1).'@demo.com'],
                [
                    'name' => "Pemilik {$storeNames[$i]}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $store = Store::firstOrCreate(
                ['store_code' => 'STR-DEMO'.str_pad($i + 1, 3, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $user->id,
                    'store_level_id' => $freeLevel->id,
                    'location_node_id' => $location->id,
                    'store_name' => $storeNames[$i],
                    'slug' => Str::slug($storeNames[$i].'-'.($i + 1)),
                    'description' => 'Toko '.$storeNames[$i].' menyediakan produk berkualitas.',
                    'phone' => '0812'.str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'email' => 'toko'.($i + 1).'@demo.com',
                    'status' => 'active',
                ]
            );

            // Generate store logo and banner
            $logoPath = 'images/stores/'.$store->store_code.'.jpg';
            $this->generatePlaceholder($logoPath, 400, 400, $store->store_name);

            $bannerPath = 'images/banners/'.$store->store_code.'.jpg';
            $this->generatePlaceholder($bannerPath, 1200, 400, $store->store_name.' banner');

            $store->update([
                'logo' => $logoPath,
                'banner' => $bannerPath,
            ]);

            $categoryIds = Category::inRandomOrder()->limit(5)->pluck('id')->toArray();
            if (empty($categoryIds)) {
                $categoryIds = Category::pluck('id')->toArray();
            }

            shuffle($productNames);
            $selected = array_slice($productNames, 0, 5);

            foreach ($selected as $idx => [$name, $price, $weight]) {
                $categoryId = $categoryIds[$idx % count($categoryIds)] ?? null;
                $product = Product::firstOrCreate(
                    ['store_id' => $store->id, 'slug' => Str::slug($name.'-'.$store->id)],
                    [
                        'category_id' => $categoryId,
                        'name' => $name,
                        'description' => "Produk {$name} dari {$store->store_name}.",
                        'status' => 'active',
                        'is_featured' => false,
                    ]
                );

                $sku = strtoupper(substr(Str::slug($name), 0, 5)).'-'.$store->id;

                $variant = ProductVariant::firstOrCreate(
                    ['store_id' => $store->id, 'sku' => $sku],
                    [
                        'product_id' => $product->id,
                        'price' => $price,
                        'stock' => mt_rand(10, 100),
                        'weight_grams' => $weight,
                        'status' => 'active',
                    ]
                );

                // Generate product image
                $productImagePath = 'images/products/'.$product->id.'.jpg';
                $this->generatePlaceholder($productImagePath, 600, 600, $product->name);

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'is_primary' => true],
                    [
                        'path' => $productImagePath,
                        'position' => 1,
                        'variant_id' => null,
                    ]
                );

                if ($idx == 0) {
                    $sizeAttr = Attribute::firstOrCreate(['name' => 'Ukuran']);
                    $colorAttr = Attribute::firstOrCreate(['name' => 'Warna']);

                    $sizes = $sizeAttr->values()->firstOrCreate(['value' => 'M']);
                    $colors = $colorAttr->values()->firstOrCreate(['value' => 'Hitam']);

                    $variant2 = ProductVariant::firstOrCreate(
                        ['store_id' => $store->id, 'sku' => $sku.'-VAR2'],
                        [
                            'product_id' => $product->id,
                            'price' => $price * 1.2,
                            'stock' => mt_rand(5, 50),
                            'weight_grams' => $weight,
                            'status' => 'active',
                        ]
                    );
                    $variant2->attributeValues()->sync([$sizes->id, $colors->id]);
                }

                if ($idx < 2) {
                    $promo = Promotion::firstOrCreate(
                        ['name' => 'Promo '.$product->name, 'source' => 'store', 'store_id' => $store->id],
                        [
                            'type' => $idx == 0 ? 'percentage' : 'fixed',
                            'value' => $idx == 0 ? 20 : 50000,
                            'starts_at' => now()->subWeek(),
                            'ends_at' => now()->addMonth(),
                            'stackable' => false,
                            'status' => 'active',
                        ]
                    );
                    $promo->products()->syncWithoutDetaching([$product->id]);
                }
            }
        }
    }

    protected function seedOrdersAndRatings(): void
    {
        // create customers
        $customers = [];
        for ($i = 0; $i < 5; $i++) {
            $customer = Customer::firstOrCreate(
                ['email' => 'customer'.($i + 1).'@demo.com'],
                [
                    'name' => 'Customer '.($i + 1),
                    'password' => Hash::make('password'),
                    'phone' => '0812'.str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $customers[] = $customer;
        }

        $stores = Store::with('products.variants')->get();
        if ($stores->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            for ($o = 0; $o < rand(2, 3); $o++) {
                $store = $stores->random();
                $products = $store->products->shuffle()->take(rand(1, 3));
                if ($products->isEmpty()) {
                    continue;
                }

                $subtotal = 0;
                $orderItemsData = [];
                foreach ($products as $product) {
                    $variant = $product->variants->first();
                    if (! $variant) {
                        continue;
                    }
                    $qty = rand(1, 3);
                    $price = $variant->price;
                    $subtotal += $price * $qty;
                    $orderItemsData[] = [
                        'product' => $product,
                        'variant' => $variant,
                        'qty' => $qty,
                        'price' => $price,
                    ];
                }
                if (empty($orderItemsData)) {
                    continue;
                }

                $discount = 0;
                $shipping = 10000;
                $total = $subtotal - $discount + $shipping;

                $invoice = Invoice::create([
                    'invoice_no' => 'INV-'.Str::random(8),
                    'customer_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'total_discount' => $discount,
                    'total_shipping_cost' => $shipping,
                    'grand_total' => $total,
                    'status' => 'paid',
                ]);

                $order = Order::create([
                    'order_no' => 'ORD-'.Str::random(8),
                    'invoice_id' => $invoice->id,
                    'store_id' => $store->id,
                    'customer_id' => $customer->id,
                    'status' => 'completed',
                    'fulfillment_type' => 'delivery',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'shipping_cost' => $shipping,
                    'total' => $total,
                    'address_snapshot' => 'Jl. Contoh No. 123, Jakarta',
                    'distance_km_snapshot' => 5.0,
                    'origin_node_snapshot' => 'Jakarta',
                    'destination_node_snapshot' => 'Jakarta',
                    'rate_per_km_snapshot' => 2000,
                    'free_distance_snapshot' => 3,
                ]);

                foreach ($orderItemsData as $item) {
                    $product = $item['product'];
                    $variant = $item['variant'];
                    $qty = $item['qty'];
                    $price = $item['price'];
                    $finalPrice = $price;
                    $subtotalItem = $price * $qty;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'name_snapshot' => $product->name,
                        'sku_snapshot' => $variant->sku,
                        'variant_snapshot' => $variant->attributeValues->pluck('value')->implode(', '),
                        'original_price_snapshot' => $price,
                        'discount_snapshot' => 0,
                        'final_price_snapshot' => $finalPrice,
                        'qty' => $qty,
                        'subtotal_snapshot' => $subtotalItem,
                    ]);
                }

                $order->load('items');

                foreach ($order->items as $orderItem) {
                    if ($order->status === 'completed') {
                        ProductRating::firstOrCreate(
                            ['order_item_id' => $orderItem->id],
                            [
                                'product_id' => $orderItem->product_id,
                                'customer_id' => $customer->id,
                                'rating' => rand(3, 5),
                                'review' => 'Produk bagus, sesuai deskripsi.',
                                'status' => 'active',
                            ]
                        );
                    }
                }
            }
        }
    }
}
