# 📊 RebateOps — Enterprise Cashback Management System

RebateOps is a professional, high-performance internal tool built with **Laravel 12** and **Filament 3** for managing large-scale cashback operations across multiple platforms. It combines a premium SaaS aesthetic with rigorous financial integrity and real-time Google Sheets synchronization.

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)
![Filament](https://img.shields.io/badge/Filament-3.2-FFAA00?style=for-the-badge&logo=filament)
![Design](https://img.shields.io/badge/Design-Premium_Mixed_Mode-blue?style=for-the-badge)

---

## 🌟 Key Pillars

### 💎 Premium Design System
- **Mixed Mode UI**: Dark elegant sidebar paired with clean, information-dense light content area.
- **Modern Typography**: Powered by **Plus Jakarta Sans** for that premium "Google Sans" feel.
- **Pixel-Perfect Dashboards**: Custom-built responsive widgets with equal-height grid layouts and micro-animations.
- **UX Excellence**: Integrated "Back to Top" functionality, optimized mobile navigation, and blur-filtered overlays.

### 🛡️ Financial Integrity & Security
- **Pessimistic Locking**: Prevents race conditions on wallet balances using `lockForUpdate()`.
- **Atomic Transactions**: All balance changes follow the **Red-Green-Refactor** safety pattern within DB transactions.
- **Advanced Data Recovery**: **SoftDeletes** implemented across all core models (Accounts, Tracker, Payouts, Brands, Methods).
- **At-Rest Encryption**: Sensitive data like Gift Card codes, PINs, and account passwords are encrypted using Laravel's native encryption.
- **Precise Calculation**: High-precision `decimal` casting for all USD/VND financial columns.

### 🔄 Bidirectional Automation
- **Queue-Powered Sync**: Real-time bidirectional sync with Google Sheets (3x retry, 60s backoff).
- **Smart Formatting**: Automatic sheet tab creation, frozen headers, and status-based conditional coloring (Live/Banned/Confirmed).
- **Activity Logging**: Full audit trail for Admin oversight on every data mutation.

---

## 👥 Role-Based Workflow

RebateOps is designed for team collaboration with strictly scoped access.

### 🛠️ Admin (The Architect)
- **Global Oversight**: Access to all accounts, emails, and trackers across all team members.
- **Wallet Control**: Manage global `PayoutMethods` and monitor real-time wallet balances.
- **Settlement Module**: Finalize payments to staff members, upload transfer proofs, and track profit margins.
- **System Integrity**: Access to Activity Logs, User Management, and global configuration.

### 👤 Staff (The Operator)
- **Account Management**: Claim and manage their assigned accounts and linked emails.
- **Operations**:
    - **Rebate Tracker**: Log and track order cashback from `Pending` to `Confirmed`.
    - **Payouts**: Execute withdrawals. Redeem Gift Cards directly in the app or provide notes for Admin-led PayPal processing.
- **Visibility**: View only their own data to ensure focused productivity and data privacy.

---

## 🗂️ Core Architecture

```
REBATEOPS
├── RESOURCE HUB          # Raw Assets
│   ├── All Platforms     # Account management
│   └── Emails            # Central email hub (linked objects)
├── WORKING SPACE         # Daily Operations
│   ├── Rebate Trackers   # Order tracking (Clicked → Confirmed)
│   └── Price Tracker     # Market rates & brand boosts
└── WALLET & PAYOUTS      # Financial Layer
    ├── Payout Logs       # Withdrawals & Liquidations
    ├── Payout Methods    # Virtual Wallets (PayPal/Bank)
    └── Settlements       # Staff Payroll & Proofs
```

---

## 🛠️ Technical Setup

### Requirements
- **PHP** 8.2+
- **Composer** & **Node.js**
- **SQLite** (Dev) or **MySQL** (Prod)
- **Google Cloud Console** access for Sheets API

### Installation
1. **Clone & Install**:
   ```bash
   git clone ...
   composer install && npm install
   ```
2. **Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Database**:
   ```bash
   touch database/database.sqlite
   php artisan migrate --force
   ```
4. **Google Auth**:
   Place your service account JSON at `storage/app/google-auth.json`.

---

## 👨‍💻 Roadmap
- [x] v5.0: Advanced Financial Locking & SoftDeletes
- [x] v5.0: Premium Mixed Mode UI Overhaul
- [ ] v5.1: Automated Profit/Loss Analytics
- [ ] v5.2: REST API for External Automation
- [ ] v5.3: Bulk Image Processing for Payment Proofs

---
<p align="center">Built for Excellence. Optimized for Profit.</p>
<p align="center"><b>© 2026 RebateOps System</b></p>
