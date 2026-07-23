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
| UI Framework | Tailwind CSS 4 |
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
│   ├── auth/                # Dashboard
│   ├── components/          # All reusable Blade components
│   ├── layouts/             # Backend layout, sidebar, header, footer
│   └── sistem/              # CRUD views (user, group, menu, hak-akses)
├── routes/                  # web.php (sistem routes)
├── src/
│   ├── Http/Controllers/    # UserController, GroupController, MenuController, HakAksesController
│   │   └── Base/            # BaseCoreController (permission middleware)
│   ├── Models/              # Menu model
│   ├── Support/             # ActiveRole helper
│   └── CoreStarterServiceProvider.php
├── stubs/                   # app.js.stub, app.css.stub, tailwind.config.snippet.js
├── SUMMARY.md               # This file
└── SETUP.md                 # Step-by-step setup guide
```

## Progress Status (Current)

### Completed

- [x] Core RBAC (Spatie permissions, role switching, permission middleware)
- [x] Menu tree management (CRUD, parent-child, sync permissions)
- [x] User CRUD with role assignment + default role
- [x] Group (Role) CRUD
- [x] Hak Akses (permission assignment per role via tabbed UI)
- [x] All Blade components (button, card, table, input, select, checkbox, radio, toggle, badge, pagination, tabs, breadcrumb, avatar, table-empty, alert, toast)
- [x] SweetAlert2 integration (confirm dialogs, toast notifications)
- [x] TailAdmin-style sidebar, header, footer, dashboard
- [x] Heroicons migration (fully replaced Font Awesome)
- [x] Dark mode (class strategy, full coverage)
- [x] Search + per-page filtering (User, Group, Hak Akses)
- [x] Icon-only circular action buttons (edit blue, delete red)
- [x] Unsaved changes warning (beforeunload + Batal confirm)
- [x] Menu tree connector visual lines
- [x] Build passes (Vite)

### Remaining / Future

- [ ] Search for Menu (tree view — needs custom logic)
- [ ] Client-side form validation (currently server-side only)
- [ ] Livewire integration for real-time datatables
- [ ] Dead components cleanup (`textarea`, `file-input`, `dropdown`, `dropdown-item`)
- [ ] Activity logging
- [ ] Profile / avatar upload
- [ ] Unit tests

## How to Continue

For a new AI agent session, read:
1. `SETUP.md` — for fresh project setup
2. Check the controllers in `src/Http/Controllers/Sistem/` for the data flow
3. Check `resources/views/components/` for available Blade components
4. Check `resources/views/sistem/` for how components are used in CRUD views

## Key Config Files

| File | Purpose |
|------|---------|
| `config/idcore.php` | Menu actions list, permission map |
| `routes/web.php` (in package) | All sistem routes |
| `stubs/app.js.stub` | Alpine config template for new projects |
| `stubs/app.css.stub` | CSS template for new projects |
