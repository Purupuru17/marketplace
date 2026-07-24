# Core Starter — Project Summary

## What is this

A **Laravel starter package** (`idcore/core-starter`) designed as the foundation for admin panel / internal tool projects. It provides:

- **Role-Based Access Control** via Spatie Permission (roles, permissions, active-role switching)
- **Menu Management** — hierarchical sidebar menu builder with auto-generated permissions per menu action
- **User & Group Management** — CRUD for users and roles
- **TailAdmin-style UI** — modern dashboard template using Tailwind CSS + Heroicons + Alpine.js + SweetAlert2
- **Reusable Blade Components** — button, card, table, form inputs, alerts, toast, breadcrumb, avatar, pagination, tabs, badges, etc.

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13, PHP 8.3 |
| UI Framework | Tailwind CSS 3 (PostCSS) |
| Icons | Blade Heroicons (`blade-ui-kit/blade-heroicons`) |
| JS | Alpine.js 3, SweetAlert2 |
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
├── stubs/                   # app.js.stub, app.css.stub, tailwind.config.snippet.js
├── SUMMARY.md               # This file
└── SETUP.md                 # Step-by-step setup guide (UUID-ready)
```

## Progress Status

### Completed

#### Core Backend
- [x] Core RBAC (Spatie permissions, role switching, permission middleware)
- [x] Menu tree management (CRUD, parent-child, sync permissions)
- [x] User CRUD with role assignment + default role
- [x] Group (Role) CRUD
- [x] Hak Akses (permission assignment per role via tabbed UI)
- [x] Login system + role switching
- [x] CheckCorePermission middleware (auto from BaseCoreController)
- [x] DataTableService — server-side processing (search, sort, paginate, JSON response)
- [x] LogController + SettingController stubs (routes registered, ready to implement)
- [x] AJAX route endpoints for user + group (user/ajax, group/ajax)

#### Frontend UI Components
- [x] 24 reusable Blade components: button, card, table, input, select, textarea,
      checkbox, radio, toggle, badge, pagination, tabs, tab-button, tab-panel,
      breadcrumb, avatar, table-empty, alert, toast, modal, dropdown, dropdown-item,
      file-input, confirm-dialog
- [x] Button variants: primary, secondary, danger, success, warning, light, dark,
      outline, outline-danger, outline-warning, outline-success, ghost
- [x] Button tooltip prop (hover reveal, group relative + span)
- [x] TailAdmin-style component polish: shadow-theme-*, rounded-2xl, dark:bg-white/[0.03],
      focus:ring-2, placeholder:text-gray-400, py-2.5 compact sizing
- [x] SweetAlert2 integration (confirm dialogs, toast notifications)
- [x] Dark mode (class strategy, full coverage on all components)

#### CRUD Views (Sistem)
- [x] Search + per-page filtering (User, Group, Hak Akses, Menu)
- [x] Icon-only circular action buttons with tooltip (outline-warning edit, outline-danger delete)
- [x] Unsaved changes warning (beforeunload + Batal confirm)
- [x] Menu tree connector visual lines
- [x] Standardized index views: `px-6 py-3` headers, correct numbering (`firstItem()+index`),
      consistent align (No center, text left, Aksi right)
- [x] Filter toolbar wrapped in `<form method="GET">` with onchange submit (all index views)
- [x] User detail page (show.blade.php)

#### Docs & Setup
- [x] SETUP.md with UUID support (HasUuids trait, uuid migration, Spatie uuid config)
- [x] Component Reference section in dashboard.blade.php (18 components with live preview
      + code snippets + props docs + Alpine magic reference + best practices)
- [x] Build passes (Vite), CSS ~96 kB

### Remaining / Future

#### High Priority
- [ ] Service & Repository layer (BaseRepository, BaseService, per-module impl)
- [ ] Refactor controllers to use Service + Repository pattern
- [ ] Activity logging — implement LogController (model, migration, UI)
- [ ] Settings module — implement SettingController (key-value store, UI)
- [ ] DataTable view toggle — simple pagination (≤100 rows) vs server-side DataTables JS (>100 rows)
- [ ] Soft deletes support in BaseRepository

#### Medium Priority
- [ ] Client-side form validation (currently server-side only)
- [ ] CSV/Excel export (Export button already in menu/index, not yet connected)
- [ ] Profile / avatar upload
- [ ] Global search across modules

#### Low Priority
- [ ] Livewire integration for real-time datatables
- [ ] API support for mobile frontend
- [ ] Unit tests
- [ ] Dead components cleanup (textarea, file-input — already functional but unpolished)

## How to Continue

For a new AI agent session, read:
1. `SETUP.md` — for fresh project setup with UUID
2. Check the controllers in `src/Http/Controllers/Sistem/` for the data flow
3. Check `resources/views/components/` for available Blade components
4. Check `resources/views/sistem/` for how components are used in CRUD views
5. `SUMMARY.md` — this file, for overall status

## Key Config Files

| File | Purpose |
|------|---------|
| `config/idcore.php` | Menu actions list, permission map |
| `routes/web.php` (in package) | All sistem routes + login routes |
| `stubs/app.js.stub` | Alpine config template for new projects |
| `stubs/app.css.stub` | CSS template for new projects |
