# 💰 RebateOps — Cashback Management System

A powerful Laravel-based internal tool for managing cashback/rebate accounts, tracking transactions, and synchronizing data bidirectionally with Google Sheets.

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![Filament](https://img.shields.io/badge/Filament-3.2-FFAA00?style=flat)
![License](https://img.shields.io/badge/License-MIT-green?style=flat)

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#️-tech-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Google Sheets Setup](#-google-sheets-setup)
- [Role System](#-role-system)
- [Navigation Structure](#-navigation-structure)
- [Key Models](#-key-models--relationships)
- [Google Sheets Sync](#-google-sheets-sync)
- [Settlement & Reconciliation](#-settlement--reconciliation)
- [Usage](#-usage)
- [Troubleshooting](#-troubleshooting)
- [Common Commands](#-common-commands)

---

## ✨ Features

### 🎯 Core Features
- **Multi-Platform Account Management** — Manage accounts across Rakuten, TopCashback, RetailMeNot, ActiveJunky, JoinHoney
- **Rebate Tracker** — Track cashback transactions per order (pending → confirmed)
- **Payout Logs** — Manage withdrawals, liquidations, and gift card transactions with automatic balance calculation
- **Email & Account Hub** — Centralized management of cashback emails and linked accounts
- **Google Sheets Sync** — Real-time bidirectional sync to Google Sheets with auto-formatting
- **Activity Logging** — Complete audit trail of all changes (admin-only)
- **Multi-User / Role-Based Access** — Admin and Staff roles with data scoping

### 🚀 Advanced Features
- **Parent–Child Transactions** — Link liquidation records to original withdrawals
- **Auto Balance Calculation** — Payout method balances update automatically on transaction completion
- **Bulk Operations** — Bulk status updates, bulk export to Google Sheets
- **Settlement / Reconciliation** — Internal payroll module for settling staff earnings per platform
- **Brand & Price Tracker** — Gift card brand management with per-brand exchange rates
- **Conditional Sheet Formatting** — Auto color-code rows by status (Live/Disabled/Limited/Confirmed)
- **Background Jobs** — Queue-based sync for non-blocking performance (retry × 3, 60s backoff)
- **Soft Deletes & Trash Restore** — All core data is recoverable

---

## 🛠️ Tech Stack

| Technology | Version | Purpose |
|---|---|---|
| **Laravel** | 12.0 | PHP Framework |
| **Filament** | 3.2 | Admin Panel |
| **Google Sheets API** | v4 (apiclient ^2.19) | Sheets Integration |
| **Spatie Activity Log** | 4.12 | Audit Trail |
| **OpenSpout** | 4.32 | Excel/CSV Import-Export |
| **filament-logger** | * | Activity Log UI in Filament |
| **SQLite** | — | Default (Development) |
| **MySQL / PostgreSQL** | — | Recommended (Production) |

---

## 📦 Requirements

- **PHP** >= 8.2
- **Composer** (latest)
- **Node.js** >= 18.x & NPM
- **SQLite** (development) or **MySQL / PostgreSQL** (production)
- **Git**

---

## 🚀 Installation

### Step 1: Clone Repository

```bash
git clone https://github.com/yuninguyen/RebateOps.git
cd RebateOps
```

### Step 2: Install Dependencies

```bash
composer install
npm install
```

### Step 3: Environment Setup

```bash
cp .env.example .env
php artisan key:generate

# SQLite (development)
touch database/database.sqlite

php artisan migrate
```

### Step 4: Create Admin User

```bash
php artisan make:filament-user
```

When prompted:
- **Name:** `Admin`
- **Email:** `admin@rebateops.local`
- **Password:** *(choose a secure password)*

> After creation, open the database and set `role = 'admin'` for this user.  
> All users created via UI default to `role = 'staff'`.

---

## 🔑 Google Sheets Setup

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (e.g., `RebateOps`)
3. Enable **Google Sheets API**: APIs & Services → Library → search "Google Sheets API" → Enable

### Step 2: Create Service Account

1. IAM & Admin → Service Accounts → **Create Service Account**
2. Name: `rebate-ops-service`
3. Skip optional steps → Done

### Step 3: Generate Credentials

1. Click the service account → **Keys** tab → Add Key → Create new key → **JSON**
2. Download, rename to `google-auth.json`
3. Move to: `storage/app/google-auth.json`

> See `google-auth.json.example` for the expected file structure.

### Step 4: Share Google Sheet

1. Open your Google Spreadsheet
2. Copy `client_email` from `google-auth.json`
3. Share the sheet with that email — set permission to **Editor**

### Step 5: Configure `.env`

```env
GOOGLE_SPREADSHEET_ID=your_spreadsheet_id_here
# How to find it: https://docs.google.com/spreadsheets/d/[SPREADSHEET_ID]/edit
```

---

## 👥 Role System

RebateOps has two roles:

| Role | Access | Description |
|---|---|---|
| `admin` | Full access | See all data, manage users, view activity logs |
| `staff` | Scoped access | See only own accounts, trackers, payouts |

### How roles work

| Area | Admin | Staff |
|---|---|---|
| User Management | ✅ Full CRUD | ❌ Hidden |
| Account list | ✅ All accounts | ✅ Own accounts only |
| Rebate Tracker | ✅ All records | ✅ Own records only |
| Payout Logs | ✅ All records | ✅ Own + child records |
| Email list | ✅ All emails | ✅ Emails linked to own accounts |
| Settlement | ✅ All staff payments | ✅ Own payments only |
| Activity Log | ✅ Yes | ❌ Hidden |

### Assigning roles

Roles are managed in **Settings → Users** (admin only).  
Options: `Admin` or `Staff`.

---

## 🗂️ Navigation Structure

```
RESOURCE HUB
├── All Platforms          (Account overview)
├── Emails                 (Email management)
├── Rakuten
├── TopCashback
├── RetailMeNot
├── ActiveJunky
└── JoinHoney

WORKING SPACE
├── All Rebate Tracker     (All platforms combined)
├── Rakuten Tracker
├── TopCashback Tracker
├── RetailMeNot Tracker
├── ActiveJunky Tracker
├── JoinHoney Tracker
└── Price Tracker

WALLET & PAYOUTS
├── Payout Logs
├── Payout Method
└── Settlement/Reconciliation

Settings  (Admin only)
├── Users
└── Activity Log
```

---

## 📊 Key Models & Relationships

### User
- **Roles:** `admin`, `staff`
- **Relations:** hasMany `Account`, hasMany `RebateTracker`, hasMany `PayoutLog`, hasMany `UserPayment`

### Account
- **Purpose:** A cashback platform account (one per email per platform)
- **Fields:** platform, email_id, password (encrypted), state, status (JSON array), device, paypal_info
- **Relations:** belongsTo `User`, belongsTo `Email`, hasMany `RebateTracker`, hasMany `PayoutLog`

### Email
- **Purpose:** Central email address shared across multiple platform accounts
- **Fields:** email, password, two_factor_code, status (Live/Disabled), note
- **Relations:** hasMany `Account`

### RebateTracker
- **Purpose:** Individual cashback transaction per order
- **Fields:** store_name, order_id, order_value, cashback_percent, rebate_amount, status, transaction_date, payout_date
- **Status flow:** Clicked → Pending → Confirmed / Ineligible / Missing
- **Relations:** belongsTo `Account`, belongsTo `User`

### PayoutLog
- **Purpose:** Withdrawal or liquidation record
- **Types:** `withdrawal` (PayPal), `hold` (Gift Card kept), `liquidation` (convert to VND)
- **Fields:** amount_usd, fee_usd, boost_percentage, net_amount_usd, exchange_rate, total_vnd, gc_brand, gc_code (encrypted), gc_pin (encrypted)
- **Parent–Child:** A `liquidation` record links to its parent `withdrawal` via `parent_id`
- **Relations:** belongsTo `Account`, belongsTo `PayoutMethod`, hasMany `children` (PayoutLog)

### PayoutMethod
- **Purpose:** Wallet or payment destination (PayPal account, bank, etc.)
- **Fields:** current_balance, exchange_rate
- **Balance logic:** `withdrawal` → balance ++, `liquidation`/`hold` → balance −−

### Brand
- **Purpose:** Gift card brand configuration
- **Fields:** name, boost (%), maximum_limit, gc_rate (per-brand exchange rate)

### UserPayment *(new in v4)*
- **Purpose:** Internal settlement record — summarizes all payouts owed to a staff member
- **Fields:** platform, transaction_type, total_usd, exchange_rate, total_vnd, status (pending/paid), payment_proof (image upload)
- **Relations:** belongsTo `User`, hasMany `PayoutLog`

---

## 🔄 Google Sheets Sync

### Automatic Sync

Data syncs automatically to Google Sheets when records are created, updated, or deleted. This is handled by:

- `PayoutLogObserver` → dispatches `SyncGoogleSheetJob`
- `RebateTrackerObserver` → dispatches `SyncGoogleSheetJob`
- `EmailObserver` → dispatches `SyncGoogleSheetJob`
- `PayoutMethodObserver` → dispatches `SyncGoogleSheetJob`

### Sheet Tab Structure

| Tab Name | Data |
|---|---|
| `Emails` | All email records |
| `Rakuten_Accounts` | Accounts per platform |
| `All_Rebate_Tracker` | All tracker records |
| `Rakuten_Tracker` | Tracker per platform |
| `Payout_Logs` | All payout records |
| `Payout_Methods` | All wallets |

### Sync Features

- **Upsert logic** — Updates existing rows, appends new ones (matched by record ID)
- **Header row** — Auto-written when a new sheet tab is created
- **Freeze + Bold header** — Row 1 is frozen and formatted automatically
- **Conditional formatting** — Rows colored by status (green = confirmed, red = limited, etc.)
- **Retry on failure** — Jobs retry up to 3× with 60s delay
- **Cache** — Sheet metadata cached 60 minutes to reduce API calls

### Manual Bulk Export

1. Go to any Resource list
2. Select records with checkboxes
3. Click **Export to Google Sheet**

---

## 💳 Settlement & Reconciliation *(new in v4)*

This module lets Admin settle earnings with Staff members.

### Workflow

1. Admin reviews completed PayoutLogs per staff per platform
2. Admin creates a `UserPayment` record summarizing the payout
3. Admin uploads payment proof (bank transfer screenshot)
4. Status changes from `Pending` → `Paid`
5. Staff can view their own settlement records (read-only)

### Access

- **Admin:** Create, edit, delete all settlement records; upload payment proof
- **Staff:** View own records only; cannot create or modify

---

## 💻 Usage

### Start Development Server

```bash
composer run dev
```

Starts all services concurrently:
- `http://localhost:8000` — Web server
- Queue worker (Google Sheets background jobs)
- Laravel Pail (log viewer)
- Vite (frontend hot reload)

### Access the Application

- **Homepage:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin

### Individual Services

```bash
php artisan serve          # Web server only
php artisan queue:work     # Queue worker (required for Sheets sync)
npm run dev                # Vite asset bundler
```

---

## 🔧 Configuration

### Database

**Development (default):**
```env
DB_CONNECTION=sqlite
```

**Production (recommended):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rebateops
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Queue

```env
QUEUE_CONNECTION=database
```

> SQLite does not handle concurrent writes well. Use MySQL in production.

---

## 🐛 Troubleshooting

### User can't log in after being created via UI

All users created through the Filament UI get `role = 'staff'` by default. Only `admin` and `staff` roles can access the panel. If a user is stuck, check their role in the database.

```sql
UPDATE users SET role = 'staff' WHERE email = 'user@example.com';
```

### Google Spreadsheet ID not configured

```
Error: "Google Spreadsheet ID is not configured"
```

Add to `.env`:
```env
GOOGLE_SPREADSHEET_ID=your_spreadsheet_id_here
```

Find the ID in your sheet URL:
```
https://docs.google.com/spreadsheets/d/[THIS_IS_THE_ID]/edit
```

### Service account file not found

```
Error: "Google service account file not found"
```

```bash
ls storage/app/google-auth.json   # Verify file exists
chmod 644 storage/app/google-auth.json
```

### Permission denied on Google Sheets

```
Error: "The caller does not have permission"
```

Share the Google Sheet with the `client_email` from `google-auth.json` and set permission to **Editor**.

### Queue not processing

```bash
# Check if worker is running
ps aux | grep queue:work

# Start it
php artisan queue:work

# Clear failed jobs
php artisan queue:flush
```

### New sheet tabs missing header row

If a new tab was created before v3 (when the header fix was applied), the first row may be data instead of headers. Re-export via **Bulk Action → Export to Google Sheet** to trigger a fresh write.

---

## 📚 Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Fresh migration (warning: drops all data)
php artisan migrate:fresh

# Create admin user
php artisan make:filament-user

# Run tests
php artisan test

# Code style fixer
./vendor/bin/pint

# List all routes
php artisan route:list

# View queue jobs
php artisan queue:monitor
```

---

## 📁 Project Structure

```
RebateOps/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # CRUD resources (15+ resources)
│   │   │   └── Traits/         # Shared schemas (HasTrackerSchema, HasAccountSchema…)
│   │   ├── Widgets/            # Dashboard widgets
│   │   └── Pages/              # Custom pages (Dashboard)
│   ├── Models/                 # Eloquent models
│   │   ├── Account.php
│   │   ├── Email.php
│   │   ├── PayoutLog.php
│   │   ├── PayoutMethod.php
│   │   ├── RebateTracker.php
│   │   ├── Brand.php
│   │   ├── UserPayment.php     ← new in v4
│   │   └── User.php
│   ├── Services/
│   │   └── GoogleSheetService.php
│   ├── Jobs/
│   │   └── SyncGoogleSheetJob.php
│   ├── Observers/              # Model event handlers
│   ├── Policies/               # Authorization policies
│   └── Console/Commands/
│       └── SyncAllToGoogleSheet.php
├── database/
│   ├── migrations/             # 46 migrations
│   └── database.sqlite
├── storage/
│   └── app/
│       └── google-auth.json    # (gitignored — add manually)
└── config/
    └── services.php
```

---

## 🔐 Security Notes

- Account passwords are stored **encrypted** in the database (`encrypted` cast)
- Gift card codes and PINs are also **encrypted** at rest
- Google credentials (`google-auth.json`) are gitignored — never commit them
- Activity logs are accessible to **admin only**
- Each staff member sees **only their own data** across all resources

---

## 🌟 Roadmap

### Version 1.x (Current)
- ✅ Multi-platform Account Management
- ✅ Rebate Tracker
- ✅ Payout Logs (withdrawal / liquidation / gift card)
- ✅ Google Sheets bidirectional sync
- ✅ Activity Logging
- ✅ Role-based access (admin / staff)
- ✅ Settlement / Reconciliation module
- ✅ Brand & Gift Card rate management

### Version 2.0 (Planned)
- [ ] RESTful API endpoints
- [ ] Advanced Analytics Dashboard
- [ ] Email Notifications
- [ ] Automated Settlement generation
- [ ] Unit & Feature test coverage

---

## 👨‍💻 Author

**Yuni Nguyen**
- GitHub: [@yuninguyen](https://github.com/yuninguyen)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) — The PHP Framework
- [Filament](https://filamentphp.com) — The Admin Panel
- [Spatie](https://spatie.be) — Laravel Packages
- [Google](https://developers.google.com/sheets/api) — Google Sheets API

---

<p align="center">Made with ❤️ by Yuni Nguyen</p>
