# Core Starter — Setup Guide

Step-by-step instructions to set up a new Laravel 13 project from scratch with `idcore/core-starter`.

## Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- MySQL / MariaDB / PostgreSQL
- Docker (optional, but recommended)

## Step 1: Create Laravel 13 Project

```bash
composer create-project laravel/laravel:^13.0 my-project
cd my-project
```

## Step 2: Install Spatie Laravel Permission

```bash
composer require spatie/laravel-permission
```

### Publish config & migration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### Add `HasRoles` trait to `User` model

```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    // ...
}
```

## Step 3: Install Additional Backend Dependencies

```bash
composer require blade-ui-kit/blade-icons blade-ui-kit/blade-heroicons
```

## Step 4: Install Frontend Dependencies

```bash
npm install alpinejs @alpinejs/collapse sweetalert2
npm install -D tailwindcss @tailwindcss/vite
```

## Step 5: Configure Vite for Tailwind

```php
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
        tailwindcss(),
    ],
});
```

## Step 6: Configure CSS

```css
/* resources/css/app.css */
@import "tailwindcss";
@import "tailwindcss-animated";

@custom-variant dark (&:is(.dark *));

@theme {
    --color-primary: #3C50E0;
    --color-primary-50: #EFF2FF;
    --color-primary-100: #DDE3FF;
    --color-primary-200: #C2CEFF;
    --color-primary-300: #9CA9F1;
    --color-primary-400: #6D7DDC;
    --color-primary-500: #465FFF;
    --color-primary-600: #3C50E0;
    --color-primary-700: #2F42B8;
    --color-primary-800: #25358F;
    --color-primary-900: #1B2866;

    --color-secondary: #80CAEE;
    --color-success: #219653;
    --color-success-50: #E9F9EB;
    --color-success-100: #D1F3D7;
    --color-success-200: #A8E6B5;
    --color-success-300: #7AD98E;
    --color-success-400: #52CC6E;
    --color-success-500: #2DBF4E;
    --color-success-600: #219653;
    --color-success-700: #1A6F3E;
    --color-success-800: #13492A;
    --color-success-900: #0C2315;

    --color-danger: #DC2626;
    --color-danger-50: #FEF2F2;
    --color-danger-100: #FEE2E2;
    --color-danger-200: #FECACA;
    --color-danger-300: #FCA5A5;
    --color-danger-400: #F87171;
    --color-danger-500: #EF4444;
    --color-danger-600: #DC2626;
    --color-danger-700: #B91C1C;
    --color-danger-800: #991B1B;
    --color-danger-900: #7F1D1D;

    --color-warning: #F59E0B;
    --color-warning-50: #FFFBEB;
    --color-warning-100: #FEF3C7;
    --color-warning-200: #FDE68A;
    --color-warning-300: #FCD34D;
    --color-warning-400: #FBBF24;
    --color-warning-500: #F59E0B;
    --color-warning-600: #D97706;
    --color-warning-700: #B45309;
    --color-warning-800: #92400E;
    --color-warning-900: #78350F;
}

@layer base {
    [x-cloak] { display: none !important; }
}
```

## Step 7: Configure JavaScript

```js
// resources/js/app.js
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

window.Swal = Swal;

document.addEventListener('alpine:init', () => {
    Alpine.store('layout', {
        sidebarOpen: false,
        collapsed: localStorage.getItem('sidebar_collapsed') === '1',
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed ? '1' : '0');
        },
    });

    Alpine.store('theme', {
        dark: localStorage.getItem('theme') === 'dark',
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        },
        init() { document.documentElement.classList.toggle('dark', this.dark); },
    });

    Alpine.store('toast', {
        items: [],
        push(message, type = 'info', duration = 4000) {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type, duration });
            if (duration > 0) setTimeout(() => this.remove(id), duration);
        },
        remove(id) { this.items = this.items.filter(item => item.id !== id); },
        success(message, d = 4000) { this.push(message, 'success', d); },
        error(message, d = 6000) { this.push(message, 'error', d); },
        warning(message, d = 5000) { this.push(message, 'warning', d); },
        info(message, d = 4000) { this.push(message, 'info', d); },
    });

    Alpine.magic('confirm', () => {
        return (options = {}) => {
            const colorMap = { danger: '#DC2626', warning: '#F59E0B', brand: '#3C50E0' };
            return Swal.fire({
                title: options.title || 'Konfirmasi',
                text: options.message || 'Apakah kamu yakin?',
                icon: options.variant === 'danger' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
                cancelButtonText: options.cancelText || 'Batal',
                confirmButtonColor: colorMap[options.variant] || '#3C50E0',
                reverseButtons: true,
            }).then(result => result.isConfirmed);
        };
    });
});

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();
```

## Step 8: Configure Layout

Update `resources/views/welcome.blade.php` (or your master layout) to use the backend layout from the package.

The default layout template is at `packages/idcore/core-starter/resources/views/layouts/backend.blade.php`. It includes:

- Sidebar with collapsible menu items
- Header with search bar, dark mode toggle, user dropdown
- Footer
- Toast notification container
- Session flash → toast via `x-init`

## Step 9: Install the Core Starter Package

### Add to `composer.json`

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/idcore/core-starter"
    }
]
```

```bash
composer require idcore/core-starter:@dev
```

### Register Service Provider

```php
// bootstrap/providers.php (Laravel 13)
return [
    // ...
    IdCore\CoreStarter\CoreStarterServiceProvider::class,
];
```

### Publish config & assets

```bash
php artisan vendor:publish --provider="IdCore\CoreStarter\CoreStarterServiceProvider"
php artisan migrate
```

### Run Seeder

```bash
php artisan db:seed --class=CoreDatabaseSeeder
```

## Step 10: Create Admin User

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
$user->assignRole('Super Admin');
$user->update(['default_role_id' => \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first()->id]);
```

## Step 11: Build Assets & Serve

```bash
npm run build
php artisan serve
```

Access at `http://localhost:8000/sistem/user`.

## Available Blade Components

| Component | Usage |
|-----------|-------|
| `x-idcore::button` | `variant` (primary/secondary/danger/success/warning/light/dark/outline/ghost), `size` (xs/sm/md/lg), `circle`, `loading`, `block`, `pill` |
| `x-idcore::card` | `title`, `subtitle`, `padding` (bool), slots: `actions`, `footer` |
| `x-idcore::table` | Wraps table in bordered container |
| `x-idcore::table-empty` | `colspan`, `message` — empty state row |
| `x-idcore::breadcrumb` | `items` array of `['label','url']` |
| `x-idcore::avatar` | `name`, `size` (sm/md/lg) |
| `x-idcore::input` / `select` / `textarea` | Form fields with label, hint, error |
| `x-idcore::checkbox` / `radio` / `toggle` | Form controls with Alpine state |
| `x-idcore::badge` | `variant` for color |
| `x-idcore::pagination` | `paginator` — Laravel paginator instance |
| `x-idcore::tabs` / `tab-button` / `tab-panel` | Tabbed UI with Alpine |
| `x-idcore::alert` | `variant` (success/error/warning/info), `dismissible` |
| `x-idcore::toast` | Auto-dismissing toast notifications via `$store.toast` |
| `x-idcore::modal` | Alpine `x-model` based modal |

## Key Alpine Utilities

| Magic | Purpose |
|-------|---------|
| `$confirm({...})` | Returns Promise<boolean> — SweetAlert2 confirm dialog |
| `$store.toast.success(msg)` / `.error()` / `.warning()` / `.info()` | Push toast notification |
| `$store.theme.dark` / `.toggle()` | Dark mode state |
| `$store.layout.sidebarOpen` / `.collapsed` | Sidebar state |
