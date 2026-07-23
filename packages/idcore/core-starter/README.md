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
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    'core_permission'    => CheckCorePermission::class,
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
    - php artisan --> route:list, route:clear, permission:cache-reset, db:seed, cache^route^config^optimize:clear
    - php artisan --> migrate:fresh
7. Tailwind
    - npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms alpinejs
    - npx tailwindcss init -p
    - WAJIB: scan semua view di package idcore/core-starter
        './packages/idcore/core-starter/resources/**/*.blade.php',
        './packages/idcore/core-starter/src/**/*.php',
    - Tambahkan x-cloak — resources/css/app.css [x-cloak] { display: none !important; }
8. 



# Setup Frontend (Tailwind + Alpine) untuk proyek baru

Setiap kali package ini dipakai di proyek baru, jalankan:

```bash
npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms
npm install alpinejs @alpinejs/collapse
```

Lalu di `tailwind.config.js` proyek utama, tambahkan ke `content`:
```javascript
'./packages/idcore/core-starter/resources/**/*.blade.php',
'./packages/idcore/core-starter/src/**/*.php',
```

Copy isi `resources/js/app.js` dan `resources/css/app.css` dari
`packages/idcore/core-starter/stubs/` (lihat bag. di bawah) ke
`resources/js/app.js` / `resources/css/app.css` milik proyek.

packages/idcore/core-starter/stubs/
├── app.js.stub      ← isi persis app.js Fase 2 kemarin (Alpine store + collapse plugin)
├── app.css.stub     ← isi persis app.css Fase 1
└── tailwind.config.snippet.js  ← potongan content[] + safelist[] yang wajib ditambahkan
