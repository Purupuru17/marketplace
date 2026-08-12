## Tutorial
1. Install Spatie --> composer require spatie/laravel-permission
2.  - Models HasRoles, HasUuids --> User.php | Fillable(['name', 'email', 'password', 'default_role_id'])
    - Migration users_table : 
        $table->uuid('id')->primary();
        $table->unsignedBigInteger('default_role_id')->nullable();
        $table->uuid('user_id')->nullable()->index();
    - Migration permission_table : 
        $table->uuid($columnNames['model_morph_key']); (model_has_permissions, model_has_roles)
        
3. Spatie Middleware --> bootstrap > app.php
    $middleware->alias([
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        'core_permission'    => CheckCorePermission::class,
    ]);
4. Call Seeder --> $this->call(CoreDatabaseSeeder::class);
5. composer.json --> require "idcore/core-starter": "dev-main"
                    "repositories": [
                        {
                            "type": "path",
                            "url": "packages/idcore/core-starter",
                            "options": { "symlink": true }
                        }
                    ]
6.  - composer --> update idcore/core-starter, dump-autoload
    - php artisan --> migrate:fresh
    - php artisan --> route:list, route:clear, permission:cache-reset, db:seed, cache^route^config^optimize:clear
    
7. Tailwind
    - npm install -D tailwindcss @tailwindcss/vite @tailwindcss/forms
    - copy app.css.stub ke resources/css/app.css
    - Tambahkan x-cloak — resources/css/app.css [x-cloak] { display: none !important; }
8. 



# Setup Frontend (Tailwind + Alpine) untuk proyek baru

Setiap kali package ini dipakai di proyek baru, jalankan:

```bash
npm install -D tailwindcss @tailwindcss/vite @tailwindcss/forms
npm install alpinejs @alpinejs/collapse
```

Tailwind v4 pakai CSS-first config, jadi tak perlu `tailwind.config.js` untuk content source.

Copy isi `resources/js/app.js` dan `resources/css/app.css` dari
`packages/idcore/core-starter/stubs/` (lihat bag. di bawah) ke
`resources/js/app.js` / `resources/css/app.css` milik proyek.

packages/idcore/core-starter/stubs/
├── app.js.stub      ← Alpine store (layout, theme, toast, confirm) + collapse plugin
└── app.css.stub     ← Tailwind v4 CSS-first tokens + source globs
