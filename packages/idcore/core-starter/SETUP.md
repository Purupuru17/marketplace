# Core Starter — Setup Guide (Laravel 13, UUID-ready)

## What is this

**Laravel starter package** (`idcore/core-starter`) designed as the foundation for admin panel / internal tool projects. It provides:

- **Role-Based Access Control** via Spatie Permission (roles, permissions, active-role switching)
- **Menu Management** — hierarchical sidebar menu builder with auto-generated permissions per menu action
- **User & Group Management** — CRUD for users and roles
- **TailAdmin-style UI** — modern dashboard template using Tailwind CSS + Heroicons + Alpine.js + SweetAlert2
- **Reusable Blade Components** — button, card, table, form inputs, alerts, toast, breadcrumb, avatar, pagination, tabs, badges, etc.

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13, PHP 8.3 |
| UI Framework | Tailwind CSS v4 (@tailwindcss/vite) |
| Icons | Blade Heroicons (`blade-ui-kit/blade-heroicons`) |
| JS | Alpine.js 3, native confirm dialog |
| Auth / RBAC | Spatie Laravel Permission |
| Build | Vite |
| Container | Docker (`app-php8`) |

## Directory Structure (Package)

```
packages/idcore/core-starter/
├── config/                  # Package config (menu_actions, permission_map)
├── database/                # Seeders (CoreDatabaseSeeder)
├── resources/views/
│   ├── auth/                # Dashboard, Login
│   ├── components/          # All reusable Blade components (24 files)
│   ├── layouts/             # Backend layout, sidebar, header, footer
│   └── sistem/              # CRUD views (user, group, menu, hak-akses)
├── routes/                  # web.php (login + sistem routes)
├── src/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/        # LoginController (login, logout, dashboard, switch-role)
│   │   │   ├── Base/        # BaseCoreController (permission middleware)
│   │   │   └── Sistem/      # UserController, GroupController, MenuController,
│   │   │                     # HakAksesController, LogController, SettingController
│   │   └── Middleware/      # CheckCorePermission
│   ├── Models/              # Menu model
│   ├── Providers/           # CoreServiceProvider
│   ├── Services/            # DataTableService (server-side DataTables)
│   └── Support/             # ActiveRole helper, MenuTreeBuilder
├── stubs/                   # app.js.stub, app.css.stub
└── SETUP.md                 # Step-by-step setup guide
```

## 1. Prasyarat
- PHP 8.3+, Composer, Node 20+, DB, Docker opsional

## 2. Buat project
```bash
composer create-project laravel/laravel:^13.0 my-project
cd my-project
```

## 3. Dependency PHP
Tambahkan repository path + package di `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/idcore/core-starter",
        "options": { "symlink": true }
    }
],
"require": {
    "idcore/core-starter": "dev-main",
    "spatie/laravel-permission": "^8.3",
    "blade-ui-kit/blade-heroicons": "^2.7",
    "blade-ui-kit/blade-icons": "^1.10"
}
```

```bash
composer update idcore/core-starter
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## 4. Model & migration (sebelum migrate)
`app/Models/User.php`:
```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasUuids, HasRoles;
    protected $fillable = ['name', 'email', 'password', 'default_role_id'];
}
```
- `users`: 
    $table->uuid('id')->primary();
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->unsignedBigInteger('default_role_id')->nullable();
- `sessions`:
    $table->uuid('user_id')->nullable()->index();
- `model_has_permissions`, `model_has_roles`:
    $table->uuid($columnNames['model_morph_key']);


## 5. Middleware
`bootstrap/app.php`:
```php
$middleware->alias([
    'permission' => PermissionMiddleware::class,
    'role' => RoleMiddleware::class,
    'role_or_permission' => RoleOrPermissionMiddleware::class,
    'core_permission' => CheckCorePermission::class,
    'active' => EnsureAccountIsActive::class,
]);
$middleware->appendToGroup('web', SecurityHeaders::class);
$middleware->appendToGroup('web', AuditActivity::class);
$middleware->trustProxies(at: '*');
```

## 6. Migrate & seed
```bash
php artisan migrate
php artisan db:seed --class=IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder
```
Atau edit `DatabaseSeeder`:
```php
$this->call(CoreDatabaseSeeder::class);
```
Akun default: `super@gmail.com` / `12345` (role `Administrator`).

## 7. Frontend
```bash
npm install -D tailwindcss @tailwindcss/vite @tailwindcss/forms
npm install alpinejs @alpinejs/collapse
```

- `vite.config.js`: input `resources/css/app.css`, `resources/js/app.js`, plugin `tailwindcss()`
- Copy stubs:
  - `packages/idcore/core-starter/stubs/app.js.stub` → `resources/js/app.js`
  - `packages/idcore/core-starter/stubs/app.css.stub` → `resources/css/app.css`
- Pastikan `@source` untuk core views/src tidak dihapus
- Tambah `[x-cloak] { display: none !important; }`

## 8. Rate limiter (opsional)
Di `AppServiceProvider::boot()`:
```php
if (config('app.env') === 'production') {
    URL::forceScheme('https');
}

RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
->by($request->input('email').'|'.$request->ip()));

RateLimiter::for('register', fn (Request $request) => Limit::perMinute(3)
->by($request->ip()));
```

## 9. Layout & build
- Gunakan layout backend package di view utama
```bash
npm run build
php artisan route:clear
php artisan permission:cache-reset
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear
php artisan serve
```

## 10. Note
- Jalankan `permission:cache-reset` setelah migrate/seed.