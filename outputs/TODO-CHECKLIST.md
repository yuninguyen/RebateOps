# ✅ TODO CHECKLIST - YUNI LEGEND

## 🔴 CRITICAL - Làm ngay hôm nay!

### 1. Chuyển Hardcoded Values sang Environment
```bash
# File cần sửa: app/Services/GoogleSheetService.php

# TRƯỚC (❌):
protected $spreadsheetId = '1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4';

# SAU (✅):
protected $spreadsheetId;

public function __construct()
{
    $this->spreadsheetId = config('services.google.spreadsheet_id');
    // ...
}
```

**Action Steps:**
- [ ] Thêm vào `.env.example`:
  ```env
  GOOGLE_SPREADSHEET_ID=
  GOOGLE_SERVICE_ACCOUNT_PATH=storage/app/google-auth.json
  ```
- [ ] Tạo `config/services.php` entry:
  ```php
  'google' => [
      'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
      'service_account_path' => env('GOOGLE_SERVICE_ACCOUNT_PATH', storage_path('app/google-auth.json')),
  ],
  ```
- [ ] Update `GoogleSheetService.php` để dùng config
- [ ] Test lại Google Sheets sync

---

### 2. Tạo google-auth.json.example
```bash
# Action Steps:
```
- [ ] Tạo file `storage/app/google-auth.json.example` với content:
  ```json
  {
    "type": "service_account",
    "project_id": "your-project-id",
    "private_key_id": "your-key-id",
    "private_key": "-----BEGIN PRIVATE KEY-----\nYOUR_PRIVATE_KEY\n-----END PRIVATE KEY-----\n",
    "client_email": "your-service-account@your-project.iam.gserviceaccount.com",
    "client_id": "your-client-id",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_x509_cert_url": "your-cert-url"
  }
  ```
- [ ] Add vào `.gitignore`:
  ```
  storage/app/google-auth.json
  ```

---

### 3. Viết README.md hoàn chỉnh
```bash
# Action Steps:
```
- [ ] Xóa README.md cũ
- [ ] Tạo README.md mới với nội dung:

```markdown
# Yuni Legend - Cashback Management System

## Description
A Laravel-based system for managing cashback/rebate accounts with Google Sheets integration.

## Requirements
- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (development) / MySQL (production)

## Installation

### 1. Clone and Install Dependencies
\`\`\`bash
git clone https://github.com/yuninguyen/yuni-legend.git
cd yuni-legend
composer install
npm install
\`\`\`

### 2. Environment Setup
\`\`\`bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
\`\`\`

### 3. Google Sheets Setup
1. Create a Google Cloud Project
2. Enable Google Sheets API
3. Create Service Account
4. Download credentials JSON
5. Save as `storage/app/google-auth.json`
6. Share your Google Sheet with the service account email
7. Add Spreadsheet ID to `.env`:
   \`\`\`
   GOOGLE_SPREADSHEET_ID=your_spreadsheet_id
   \`\`\`

### 4. Create Admin User
\`\`\`bash
php artisan make:filament-user
\`\`\`

### 5. Run Application
\`\`\`bash
composer run dev
\`\`\`

Access: http://localhost:8000/admin

## Features
- Account Management
- Payout Tracking (Withdrawal/Liquidation)
- Rebate Tracking
- Google Sheets Sync
- Activity Logging

## Tech Stack
- Laravel 12
- Filament 3.2
- Google Sheets API
- Spatie Activity Log

## License
MIT
```

---

### 4. Add Error Handling
```bash
# File cần sửa: app/Services/GoogleSheetService.php

# Các methods cần wrap try-catch:
```

- [ ] `appendRow()` method
- [ ] `updateSheet()` method
- [ ] `readSheet()` method
- [ ] `upsertRows()` method

**Example:**
```php
public function appendRow(array $data, ?string $sheetName = null)
{
    try {
        $targetSheet = $sheetName ?? $this->getFirstSheetName();
        $values = [$data];
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];

        return $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            "'{$targetSheet}'!A1",
            $body,
            $params
        );
    } catch (\Google\Service\Exception $e) {
        \Log::error('Google Sheets API Error - Append Row', [
            'error' => $e->getMessage(),
            'data' => $data,
            'sheet' => $sheetName
        ]);
        throw new \Exception('Failed to append data to Google Sheets: ' . $e->getMessage());
    }
}
```

---

## 🟡 HIGH PRIORITY - Làm tuần này

### 5. Setup Authentication
- [ ] Cấu hình Filament User model
- [ ] Setup roles & permissions (Optional: Spatie Laravel Permission)
- [ ] Tạo seeders cho admin user
- [ ] Test login/logout flow

### 6. Migrate to MySQL/PostgreSQL
```bash
# Chuẩn bị production database
```
- [ ] Install MySQL/PostgreSQL
- [ ] Update `.env`:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=yuni_legend
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- [ ] Run migrations: `php artisan migrate:fresh`
- [ ] Verify data

### 7. Write Basic Tests
```bash
# Tạo test files
```
- [ ] `tests/Unit/Models/AccountTest.php`
- [ ] `tests/Unit/Models/PayoutLogTest.php`
- [ ] `tests/Feature/GoogleSheetServiceTest.php`

**Example Test:**
```php
// tests/Unit/Models/PayoutLogTest.php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PayoutLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayoutLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_net_amount_correctly()
    {
        $payout = PayoutLog::factory()->create([
            'amount_usd' => 100,
            'fee_usd' => 5,
            'boost_percentage' => 2,
        ]);

        $this->assertEquals(97, $payout->net_amount_usd);
    }
}
```

- [ ] Run tests: `php artisan test`

---

## 🟢 MEDIUM PRIORITY - Làm tháng này

### 8. Improve Code Documentation
- [ ] Add PHPDoc cho tất cả public methods
- [ ] Chuyển comments sang tiếng Anh
- [ ] Document business logic phức tạp

### 9. Setup Git Hooks
```bash
# Install pre-commit hooks
```
- [ ] Install PHP CS Fixer
- [ ] Create `.php-cs-fixer.php` config
- [ ] Setup pre-commit hook để format code

### 10. Create Deployment Guide
```bash
# Tạo file DEPLOYMENT.md
```
- [ ] Server requirements
- [ ] Installation steps
- [ ] Environment setup
- [ ] Backup strategy
- [ ] Rollback procedures

### 11. Database Documentation
- [ ] Create ER diagram
- [ ] Document table relationships
- [ ] Add migration squash plan

### 12. Add Monitoring
- [ ] Setup Laravel Telescope (development)
- [ ] Add Sentry for error tracking (production)
- [ ] Create health check endpoint

---

## 📝 NICE TO HAVE - Làm khi rảnh

### 13. Dashboard Improvements
- [ ] Add charts (Filament Widgets)
- [ ] Revenue analytics
- [ ] Account health metrics

### 14. Export Features
- [ ] Export accounts to Excel
- [ ] Export payouts to PDF
- [ ] Scheduled reports

### 15. Notifications
- [ ] Email notifications for important events
- [ ] Slack integration
- [ ] SMS alerts

### 16. API Development
- [ ] Create API endpoints
- [ ] Setup Laravel Sanctum
- [ ] API documentation (Swagger)

### 17. Performance Optimization
- [ ] Add Redis caching
- [ ] Database query optimization
- [ ] Lazy loading optimization

---

## 📊 CHECKLIST SUMMARY

### Week 1 (Critical)
- [x] Read this checklist
- [ ] Move hardcoded values to .env
- [ ] Create google-auth.json.example
- [ ] Write README.md
- [ ] Add error handling

### Week 2 (High Priority)
- [ ] Setup authentication
- [ ] Migrate to MySQL
- [ ] Write basic tests
- [ ] Code review & refactor

### Week 3-4 (Medium Priority)
- [ ] Improve documentation
- [ ] Setup CI/CD
- [ ] Add monitoring
- [ ] Performance testing

### Month 2+ (Nice to Have)
- [ ] Dashboard improvements
- [ ] API development
- [ ] Advanced features

---

## 🎯 PROGRESS TRACKER

**Overall Completion: 0/17 tasks** (0%)

### By Priority:
- Critical (4 tasks): 0/4 ⬜⬜⬜⬜
- High Priority (3 tasks): 0/3 ⬜⬜⬜
- Medium Priority (5 tasks): 0/5 ⬜⬜⬜⬜⬜
- Nice to Have (5 tasks): 0/5 ⬜⬜⬜⬜⬜

---

**Last Updated:** March 5, 2026  
**Version:** 1.0

📌 **Tip:** Check off items as you complete them and update the progress tracker!
