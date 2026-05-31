# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Full dev environment (Artisan serve + queue + Pail logs + Vite, all concurrent)
composer run dev

# Run tests (clears config cache first)
composer run test

# Run a single test file or filter
php artisan test --filter TestClassName
php artisan test tests/Feature/SomeTest.php

# Lint PHP with Pint
./vendor/bin/pint

# First-time setup
composer run setup

# Frontend assets only
npm run build   # production
npm run dev     # watch (already included in composer run dev)

# DB utilities
php artisan app:db-export
php artisan app:db-import
```

## Architecture

**Stack:** Laravel 13 / PHP 8.3+, Blade + Tailwind CSS v4, Vite 8, MySQL (Docker) or SQLite (local/tests).

**App locale is Spanish** (`es`). Route names, URL slugs, and UI labels are all in Spanish (e.g. `/cuentas`, `/movimientos`, `/pendientes`, `etiquetas`).

### Multi-tenancy: FamilyGroup

Every resource belongs to a `FamilyGroup`. Users belong to one or more groups (many-to-many). The **active group** is stored in the session as `active_family_group_id`.

The `EnsureUserBelongsToGroup` middleware (applied to nearly all routes) reads this session value and injects it into the request as `_active_family_group_id`. Controllers retrieve the active group with:

```php
$groupId = $request->input('_active_family_group_id');
```

### Key models and relationships

- `FamilyGroup` → has many `Account`, `Transaction`, `Category`, `PaymentItem`, `Tag`, `ExchangeRate`, `Promotion`
- `Transaction` → belongs to `FamilyGroup`, `User`, `Account`, `Category`; has many `Installment`; morphable `Tag` pivot
- `Account` → has many `Transaction`, `LoanInstallment` (loan repayment schedule)
- `PaymentItem` → recurring payment templates; generates `MonthlyPayment` records per month; supports `dispensable` flag
- `Tag` → polymorphic: can tag both `Transaction` and `PaymentItem`

### TransactionService

`app/Services/TransactionService.php` wraps creation and update of transactions in DB transactions. It handles installment generation (`Installment` records) when `has_installments` is true. Use this service instead of creating `Transaction` models directly.

### Route structure

```
/                          dashboard
/cuentas                   accounts (resource)
/movimientos               transactions (resource + card payments)
/prestamos/{account}/plan  loan installment schedule
/pendientes                monthly payment checklist
/reportes                  spending reports + PDF export
/tipo-de-cambio            exchange rates
/etiquetas                 tags
/promociones               promotions
/importar                  CSV import
/categorias                categories
/grupos, /setup            family group management
```

### Views

Blade templates live in `resources/views/`. Shared layouts are in `layouts/`, reusable partials in `components/`. Each resource has its own subdirectory matching the route slug.

### Testing

Tests use SQLite in-memory (configured in `phpunit.xml`). Feature tests are in `tests/Feature/`, unit tests in `tests/Unit/`.

### Deployment

Deployed on Fly.io (`fly.toml`). Docker-based with `Dockerfile` (dev) and `Dockerfile.prod` (production). Docker Compose provides MySQL 8, Nginx, Redis, and PHP-FPM locally.
