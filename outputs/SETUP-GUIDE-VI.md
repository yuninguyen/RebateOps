# 🇻🇳 HƯỚNG DẪN CÀI ĐẶT - YUNI LEGEND

> Hướng dẫn chi tiết bằng tiếng Việt cho người mới bắt đầu

---

## 📋 MỤC LỤC

1. [Chuẩn bị](#1-chuẩn-bị)
2. [Cài đặt dự án](#2-cài-đặt-dự-án)
3. [Cấu hình Google Sheets](#3-cấu-hình-google-sheets)
4. [Chạy ứng dụng](#4-chạy-ứng-dụng)
5. [Xử lý lỗi](#5-xử-lý-lỗi)

---

## 1️⃣ CHUẨN BỊ

### Bạn cần cài đặt các công cụ sau:

#### ✅ PHP 8.2 trở lên

**Kiểm tra PHP:**
```bash
php -v
```

**Nếu chưa có, tải về:**
- **Windows:** https://windows.php.net/download/
- **Mac:** `brew install php@8.2`
- **Linux:** `sudo apt install php8.2`

---

#### ✅ Composer

**Kiểm tra Composer:**
```bash
composer -v
```

**Nếu chưa có:**
1. Vào https://getcomposer.org/download/
2. Tải về và cài đặt
3. Khởi động lại Terminal

---

#### ✅ Node.js & NPM

**Kiểm tra:**
```bash
node -v
npm -v
```

**Nếu chưa có:**
1. Vào https://nodejs.org/
2. Tải phiên bản LTS
3. Cài đặt (Next → Next → Next...)

---

#### ✅ Git

**Kiểm tra:**
```bash
git --version
```

**Nếu chưa có:**
- Tải về: https://git-scm.com/downloads

---

## 2️⃣ CÀI ĐẶT DỰ ÁN

### Bước 1: Clone dự án về máy

```bash
# Mở Terminal (Mac/Linux) hoặc Command Prompt (Windows)
# Di chuyển đến thư mục muốn lưu dự án
cd Desktop

# Clone dự án
git clone https://github.com/yuninguyen/yuni-legend.git

# Vào thư mục dự án
cd yuni-legend
```

---

### Bước 2: Cài đặt các dependencies

```bash
# Cài đặt PHP packages (mất khoảng 2-3 phút)
composer install

# Cài đặt JavaScript packages (mất khoảng 1-2 phút)
npm install
```

**Lưu ý:** Nếu gặp lỗi, thử:
```bash
composer install --ignore-platform-reqs
```

---

### Bước 3: Tạo file môi trường (.env)

```bash
# Copy file .env.example thành .env
cp .env.example .env

# Hoặc trên Windows:
copy .env.example .env
```

---

### Bước 4: Tạo App Key

```bash
php artisan key:generate
```

**Kết quả:** Bạn sẽ thấy thông báo "Application key set successfully"

---

### Bước 5: Tạo database

```bash
# Tạo file SQLite database
touch database/database.sqlite

# Hoặc trên Windows, tạo file rỗng bằng cách:
# 1. Vào thư mục database/
# 2. Click chuột phải → New → Text Document
# 3. Đổi tên thành "database.sqlite" (bỏ phần .txt)
```

---

### Bước 6: Chạy migrations (Tạo bảng trong database)

```bash
php artisan migrate
```

**Kết quả:** Bạn sẽ thấy danh sách các bảng được tạo:
```
✓ create_users_table
✓ create_accounts_table
✓ create_payout_logs_table
...
```

---

### Bước 7: Tạo tài khoản Admin

```bash
php artisan make:filament-user
```

**Nhập thông tin:**
```
Name: Admin
Email: admin@example.com
Password: ******** (tự chọn password mạnh)
```

**Lưu lại thông tin này để đăng nhập sau!**

---

## 3️⃣ CẤU HÌNH GOOGLE SHEETS

### Bước 1: Tạo Google Cloud Project

1. **Truy cập:** https://console.cloud.google.com/
2. **Đăng nhập** bằng tài khoản Google
3. **Click** "Select a project" ở góc trên
4. **Click** "New Project"
5. **Nhập:**
   - Project name: `Yuni-Legend`
   - Location: `No organization`
6. **Click** "Create"

---

### Bước 2: Bật Google Sheets API

1. **Vào menu** (≡) → "APIs & Services" → "Library"
2. **Tìm kiếm:** "Google Sheets API"
3. **Click vào** "Google Sheets API"
4. **Click** "Enable"
5. **Đợi** 10-20 giây để API được kích hoạt

---

### Bước 3: Tạo Service Account

1. **Vào menu** (≡) → "IAM & Admin" → "Service Accounts"
2. **Click** "Create Service Account"
3. **Nhập thông tin:**
   - Service account name: `yuni-sheets-service`
   - Service account ID: (tự động tạo)
   - Description: `Dùng để sync Google Sheets`
4. **Click** "Create and Continue"
5. **Bỏ qua** Grant access (Click "Continue")
6. **Bỏ qua** Grant users access (Click "Done")

---

### Bước 4: Tải credentials (Quan trọng!)

1. **Click vào** service account vừa tạo
2. **Chuyển sang tab** "Keys"
3. **Click** "Add Key" → "Create new key"
4. **Chọn** "JSON"
5. **Click** "Create"
6. **File JSON sẽ tự động tải về máy**

---

### Bước 5: Copy file credentials vào dự án

1. **Đổi tên file** vừa tải về thành: `google-auth.json`
2. **Copy file** vào thư mục dự án:
   ```
   yuni-legend/storage/app/google-auth.json
   ```

**Cấu trúc thư mục:**
```
yuni-legend/
└── storage/
    └── app/
        ├── google-auth.json         ← File bạn vừa copy
        └── google-auth.json.example ← File mẫu
```

---

### Bước 6: Chia sẻ Google Sheet với Service Account

1. **Mở file** `google-auth.json`
2. **Tìm dòng** `"client_email":`
   ```json
   "client_email": "yuni-sheets-service@yuni-legend-xxxx.iam.gserviceaccount.com"
   ```
3. **Copy email** này

4. **Mở Google Sheet** của bạn
5. **Click** nút "Share" (Chia sẻ) góc phải
6. **Dán email** vừa copy
7. **Chọn quyền:** "Editor" (Người chỉnh sửa)
8. **Bỏ tick** "Notify people"
9. **Click** "Share"

---

### Bước 7: Lấy Spreadsheet ID

**Cách 1: Từ URL**
```
https://docs.google.com/spreadsheets/d/[COPY_ĐOẠN_NÀY]/edit
                                        ↑
                                  Đây là ID
```

**Ví dụ:**
```
URL: https://docs.google.com/spreadsheets/d/1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4/edit

ID: 1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4
```

---

### Bước 8: Cập nhật file .env

1. **Mở file** `.env` (trong thư mục gốc dự án)
2. **Tìm dòng:**
   ```env
   GOOGLE_SPREADSHEET_ID=
   ```
3. **Dán ID** vào sau dấu `=`:
   ```env
   GOOGLE_SPREADSHEET_ID=1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4
   ```
4. **Lưu file**

---

## 4️⃣ CHẠY ỨNG DỤNG

### Cách 1: Chạy tất cả cùng lúc (Khuyến nghị)

```bash
composer run dev
```

**Điều này sẽ chạy:**
- ✅ Web server (http://localhost:8000)
- ✅ Queue worker (xử lý background jobs)
- ✅ Log viewer
- ✅ Vite dev server (hot reload)

---

### Cách 2: Chạy từng service riêng

**Terminal 1:** Web server
```bash
php artisan serve
```

**Terminal 2:** Queue worker
```bash
php artisan queue:work
```

**Terminal 3:** Vite
```bash
npm run dev
```

---

### Truy cập ứng dụng

**Mở trình duyệt:**
- **Trang chính:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin

**Đăng nhập:**
- Email: `admin@example.com`
- Password: (password bạn đã tạo ở bước 2.7)

---

## 5️⃣ XỬ LÝ LỖI

### ❌ Lỗi: "composer: command not found"

**Nguyên nhân:** Chưa cài Composer

**Giải pháp:**
1. Tải Composer: https://getcomposer.org/download/
2. Cài đặt
3. Khởi động lại Terminal
4. Thử lại: `composer -v`

---

### ❌ Lỗi: "npm: command not found"

**Nguyên nhân:** Chưa cài Node.js

**Giải pháp:**
1. Tải Node.js: https://nodejs.org/
2. Cài đặt phiên bản LTS
3. Khởi động lại Terminal
4. Thử lại: `npm -v`

---

### ❌ Lỗi: "could not find driver"

**Nguyên nhân:** Thiếu SQLite extension

**Giải pháp Windows:**
1. Tìm file `php.ini` (chạy `php --ini`)
2. Mở bằng Notepad
3. Tìm và bỏ dấu `;` trước:
   ```ini
   ;extension=pdo_sqlite
   ;extension=sqlite3
   ```
   Thành:
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```
4. Lưu file
5. Khởi động lại Terminal

**Giải pháp Linux:**
```bash
sudo apt install php8.2-sqlite3
```

**Giải pháp Mac:**
```bash
brew install sqlite3
```

---

### ❌ Lỗi: "Google Spreadsheet ID is not configured"

**Nguyên nhân:** Chưa thêm ID vào .env

**Giải pháp:**
1. Mở file `.env`
2. Kiểm tra dòng:
   ```env
   GOOGLE_SPREADSHEET_ID=your_id_here
   ```
3. Đảm bảo có ID (không để trống)
4. Lưu file
5. Khởi động lại server: `php artisan serve`

---

### ❌ Lỗi: "Service account file not found"

**Nguyên nhân:** File `google-auth.json` không đúng vị trí

**Giải pháp:**
1. Kiểm tra file có tồn tại:
   ```bash
   ls storage/app/google-auth.json
   ```
2. Nếu không có, copy lại file JSON vào đúng vị trí
3. Kiểm tra quyền file:
   ```bash
   chmod 644 storage/app/google-auth.json
   ```

---

### ❌ Lỗi: "The caller does not have permission"

**Nguyên nhân:** Chưa share Google Sheet với service account

**Giải pháp:**
1. Mở file `google-auth.json`
2. Copy `"client_email"`
3. Mở Google Sheet
4. Click "Share"
5. Dán email
6. Chọn quyền "Editor"
7. Click "Share"

---

### ❌ Lỗi: "Port 8000 already in use"

**Nguyên nhân:** Port 8000 đang được dùng

**Giải pháp 1:** Dùng port khác
```bash
php artisan serve --port=8080
```

**Giải pháp 2:** Tắt process đang dùng port
```bash
# Windows
netstat -ano | findstr :8000
taskkill /PID [PID_NUMBER] /F

# Mac/Linux
lsof -ti:8000 | xargs kill
```

---

## 🎉 HOÀN TẤT!

Nếu mọi thứ chạy tốt, bạn sẽ thấy:

```bash
✅ Server started: http://localhost:8000
✅ Queue worker started
✅ Application ready
```

**Bước tiếp theo:**
1. Truy cập http://localhost:8000/admin
2. Đăng nhập
3. Bắt đầu sử dụng!

---

## 📚 CÁC LỆNH THƯỜNG DÙNG

```bash
# Chạy ứng dụng (development)
composer run dev

# Xóa cache
php artisan cache:clear
php artisan config:clear

# Chạy migrations
php artisan migrate

# Tạo admin user mới
php artisan make:filament-user

# Xem routes
php artisan route:list

# Chạy tests
php artisan test
```

---

## 🆘 CẦN GIÚP ĐỠ?

Nếu gặp vấn đề:

1. **Đọc lại hướng dẫn** - Có thể bạn đã bỏ sót bước nào
2. **Kiểm tra logs** - File `storage/logs/laravel.log`
3. **Google lỗi** - Copy thông báo lỗi và search
4. **Hỏi cộng đồng:**
   - Laravel Vietnam: https://www.facebook.com/groups/vietnam.laravel/
   - Stack Overflow: tag `laravel`

---

## ✅ CHECKLIST HOÀN THÀNH

- [ ] Cài PHP, Composer, Node.js, Git
- [ ] Clone dự án về máy
- [ ] Chạy `composer install`
- [ ] Chạy `npm install`
- [ ] Copy .env.example → .env
- [ ] Chạy `php artisan key:generate`
- [ ] Tạo database.sqlite
- [ ] Chạy migrations
- [ ] Tạo admin user
- [ ] Tạo Google Cloud Project
- [ ] Enable Google Sheets API
- [ ] Tạo Service Account
- [ ] Tải credentials JSON
- [ ] Copy vào storage/app/google-auth.json
- [ ] Share Google Sheet
- [ ] Thêm Spreadsheet ID vào .env
- [ ] Chạy ứng dụng thành công
- [ ] Truy cập admin panel
- [ ] Đăng nhập thành công

---

**Chúc bạn thành công! 🚀**
