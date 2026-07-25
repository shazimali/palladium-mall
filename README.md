# 🏢 Palladium Mall — Commercial Real Estate & ERP Financial Management System

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.3](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=black)](https://alpinejs.dev)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**Palladium Mall Management & ERP Suite** is a full-featured, enterprise-grade Commercial Real Estate Operations & Financial Management System built with **Laravel 12**, **Tailwind CSS v4**, **Alpine.js**, and **Vite**. 

Designed specifically for shopping centers, commercial plazas, and multi-tenant complexes, this platform automates lease agreement lifecycles, complex multi-bill receiving voucher allocations, utility & breaker inspection tracking, real-time cash flow Profit & Loss analysis, and managing owner equity distributions.

---

## 🚀 Executive Highlights & Key Modules

### 🏬 1. Commercial Real Estate & Inventory Management
* **Unit Ownership Matrix**: Track both self-owned (**PM Mall Units**) and investor-owned (**Other / Landlord-Owned Units**) with custom management commission structures.
* **Electrical Circuit Breakers & Inspections**: Track breaker status, power capacities, and routine physical maintenance inspections per unit.
* **Utility Meter Tracking**: Dedicated tracking for electricity, water, and gas meters with historical consumption logs and tariff calculations.

### 📜 2. Lease Agreement & Tenant Lifecycle
* **Multi-Step Onboarding Wizard**: Step-by-step tenant contract creation with automated security deposit billing and rent schedule generation.
* **Tenant Move-Out Engine**: Automated lease termination processing, outstanding bill settlement checks, and security deposit refund calculations.
* **Multi-Unit Tenancy Support**: Seamlessly manage tenants leasing multiple commercial shops or offices under a single master entity.

### 💳 3. Billing & Multi-Allocation Voucher System
* **Automated Monthly Billing Generation**: Generate batch recurring monthly bills for Rent, Maintenance, Security Deposits, Utility Bills (Electricity, Water, Gas), and Late Fines.
* **Smart Partial Allocation Engine**: Custom Receiving Voucher (RV) system supporting split-payment allocations across multiple billing records and advance payment handling.
* **General Receiving Vouchers (GRV)**: Record miscellaneous party receipts with instant general ledger integration.

### 📊 4. Financial Intelligence & Business Reporting
* **Cash-Flow Profit & Loss (P&L)**: Real-time cash-basis P&L statements with dynamic date filtering (`date_from` & `date_to`), strictly excluding non-revenue liabilities like refundable security deposits.
* **Dual Monthly Matrix Reports**:
  * **Generated Billings Matrix**: Accrual view of active billings and collections per unit.
  * **Expected Revenue Matrix**: Projections based on active lease agreements and unit defaults.
* **Managing Owner Dues Statement**: Automated equity-based profit distribution engine calculating partner shares according to ownership percentages.
* **Cash Book & Party Ledgers**: Double-entry ledger tracking for vendor parties, opening balances, and owner capital withdrawals.
* **PDF & Excel Export**: Export financial reports, matrix statements, and invoice receipts using `Barryvdh DomPDF` and `Laravel Excel (Maatwebsite)`.

---

## 🛠️ Architecture & Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Backend Framework** | Laravel 12.x (PHP 8.2+) |
| **Frontend UI** | Blade Templating + Tailwind CSS v4 |
| **Client Reactivity** | Alpine.js 3.x + Flatpickr + ApexCharts |
| **Build Tool** | Vite |
| **Database ORM** | Eloquent ORM (MySQL / SQLite / PostgreSQL) |
| **PDF Generation** | `barryvdh/laravel-dompdf` |
| **Excel Export** | `maatwebsite/excel` |

---

## 🧠 Technical Engineering Highlights (Interview Focus)

### 1. Cash-Basis vs. Accrual Reconciliation Engine
Traditional accounting systems struggle to reconcile **Cash Flow (when money is physically received)** with **Accrual Billings (the target period rent is owed for)**. Palladium Mall solves this by decoupling Receiving Vouchers from Payment line items via a pivot table (`receiving_voucher_payments`). This allows:
* P&L reporting on actual voucher transaction dates.
* Monthly Matrix views based on billing periods (`month`).
* Accurate advance payment allocation without double-counting revenue.

### 2. Atomic Split-Payment Allocation Logic
When a tenant submits a single lump-sum voucher (e.g. $100,000 for Rent, Maintenance, and Utilities across multiple months), the system atomically distributes `amount_allocated` across selected `Payment` records in a single database transaction, ensuring data consistency and real-time balance calculations.

### 3. Asynchronous Live Filtering & Pagination
The billing and financial tables feature debounced live search, owner type toggles (`PM Mall` vs. `Other-Owned`), and date range pickers implemented via asynchronous Blade AJAX partial renders without requiring full page reloads.

---

## 📋 System Requirements

* **PHP**: 8.2 or higher
* **Composer**: 2.x
* **Node.js**: 18.x or higher
* **Database**: MySQL 8.0+ / MariaDB / SQLite / PostgreSQL

---

## ⚙️ Quick Start Setup Guide

### 1. Clone & Install Dependencies

```bash
git clone https://github.com/your-username/palladium-mall.git
cd palladium-mall

# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=palladium_mall_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Database Migrations & Seeders

```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Launch Development Environment

Run the unified development command (starts Laravel server, Vite, queue listener, and Pail logs):

```bash
composer run dev
```

Visit the application at: `http://localhost:8000`

---

## 📄 License & Credits

Built with ❤️ for commercial real estate management. Distributed under the [MIT License](LICENSE).
