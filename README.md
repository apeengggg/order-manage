# Order Management System

Sistem manajemen order dengan role-based access control (RBAC), dynamic menu, dan permission granular per modul.

## Fitur Utama

- **Login & Authentication** - Session-based login dengan bcrypt password
- **Role-Based Access Control** - Admin & Customer Service (CS) dengan permission berbeda
- **Dynamic Menu** - Menu sidebar tampil otomatis berdasarkan permission user
- **Permission Management** - 7 tipe permission per modul: View, Add, Edit, Delete, View Detail, Upload, Download
- **Module Management** - Tambah/edit/hapus modul menu dengan icon picker FontAwesome
- **CRUD Order** - Input, list, edit, hapus order customer
- **Export Order (Admin)** - Pilih ekspedisi, export ke CSV. Order yang sudah diexport terkunci (tidak bisa edit/delete oleh CS)
- **Kelola Ekspedisi** - CRUD data ekspedisi/kurir
- **File Upload** - Upload file generic untuk semua modul, support thumbnail preview, multiple storage driver
- **Storage Driver** - Support Local storage dan MinIO (S3-compatible), configurable via `.env`
- **Error Pages** - Halaman 404 (Not Found) dan 403 (Forbidden) yang responsif
- **UI Modern** - AdminLTE 3, DataTables, Select2, SweetAlert2, Toastr

## Requirement

- PHP >= 7.4 (with GD extension for thumbnail)
- MySQL >= 5.7
- Composer
- Apache dengan mod_rewrite (atau nginx, atau PHP built-in server)

## Instalasi

### 1. Clone / Download Project

```bash
git clone <repo-url> order-management
cd order-management
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Database

Import file SQL ke MySQL:

```bash
mysql -u root -p < database.sql
```

Atau import via phpMyAdmin: buka `database.sql` dan jalankan.

### 4. Konfigurasi Environment

Copy `.env.example` ke `.env` dan sesuaikan:

```bash
cp .env.example .env
```

Edit `.env`:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=order_management
APP_NAME="Order Management System"

# Storage: "local" atau "minio"
STORAGE_DRIVER=local

# MinIO (hanya jika STORAGE_DRIVER=minio)
MINIO_ENDPOINT=localhost:9000
MINIO_BUCKET=uploads
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_REGION=us-east-1
MINIO_USE_SSL=false
```

### 5. Jalankan Server

**Cara Tercepat - PHP Built-in Server:**

```bash
php -S localhost:8000 -t public public/router.php
```

Buka: `http://localhost:8000`

**Apache (XAMPP/Laragon):**
- Pastikan `mod_rewrite` aktif
- Arahkan DocumentRoot ke folder `public/`, atau akses via `http://localhost/order-management/public/`

**Nginx:**
```nginx
root /path/to/project/public;

location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

### 6. Akses Aplikasi

Buka browser: `http://localhost:8000`

## Default Login

| Username | Password   | Role  |
|----------|-----------|-------|
| admin    | admin123  | Admin |
| cs1      | admin123  | CS    |
| cs2      | admin123  | CS    |

## Struktur Folder

```
project/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── OrderController.php
│   │   ├── AdminController.php
│   │   ├── ExpeditionController.php
│   │   ├── FileController.php         # Generic file upload/download/serve
│   │   ├── PermissionController.php
│   │   └── ModuleController.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── OrderService.php
│   │   ├── ExpeditionService.php
│   │   ├── FileService.php            # Generic file upload logic
│   │   └── PermissionService.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── OrderRepository.php
│   │   ├── ExpeditionRepository.php
│   │   ├── FileRepository.php         # File DB operations
│   │   └── PermissionRepository.php
│   └── Storage/
│       ├── StorageInterface.php       # Storage driver contract
│       ├── StorageFactory.php         # Driver factory (from .env)
│       ├── LocalStorage.php           # Local filesystem driver
│       └── MinioStorage.php           # MinIO / S3-compatible driver
├── config/
│   ├── app.php                        # Helper functions, env(), session
│   └── database.php                   # Koneksi database (uses .env)
├── storage/
│   └── uploads/                       # File uploads (local driver)
│       ├── {module}/                   # Folder per module
│       │   └── {id}/                   # Folder per record ID
│       │       ├── original_file.jpg
│       │       └── thumb_original.jpg  # Auto-generated thumbnail
│       └── .gitkeep
├── views/
│   ├── layouts/
│   │   ├── header.php
│   │   ├── navbar.php
│   │   ├── sidebar.php                # Dynamic sidebar menu
│   │   └── footer.php                 # JS + global flash handler
│   ├── errors/
│   │   ├── 403.php                    # Forbidden access page
│   │   └── 404.php                    # Not found page
│   ├── auth/login.php
│   ├── dashboard/index.php
│   ├── orders/
│   ├── admin/index.php
│   ├── expeditions/index.php          # With file upload example
│   ├── permissions/index.php
│   └── modules/index.php
├── public/
│   ├── index.php                      # Front controller
│   ├── router.php                     # PHP built-in server router
│   ├── .htaccess
│   └── js/
│       ├── app.js                     # Global utilities (SweetAlert, Toastr, etc)
│       ├── file-upload.js             # Reusable file upload component
│       ├── admin-export.js
│       ├── permissions.js
│       ├── modules.js
│       └── expeditions.js
├── database.sql                       # SQL schema + seed data
├── composer.json                      # PSR-4 autoload config
├── .env                               # Environment config (not in git)
├── .env.example                       # Template environment
├── .gitignore
└── README.md
```

## File Upload System

### Konsep

Sistem upload file bersifat **generic/global** - bisa digunakan oleh modul apapun. File disimpan di tabel `files` dengan kolom `module` dan `module_id` sebagai referensi ke record terkait.

### Storage Driver

Pilih storage driver di `.env`:

**Local Storage** (`STORAGE_DRIVER=local`):
- File disimpan di `storage/uploads/{module}/{id}/`
- Thumbnail otomatis dibuat untuk gambar
- File di-serve melalui `FileController::serve()` (bukan akses langsung)

**MinIO** (`STORAGE_DRIVER=minio`):
- File disimpan di MinIO bucket
- Kompatibel S3 API
- Cocok untuk production/cloud deployment

### Cara Menggunakan di Modul Lain

**PHP (Backend):**

```php
// Upload file
$fileService = new \App\Services\FileService();
$result = $fileService->upload($_FILES['file'], 'orders', $orderId, auth('user_id'));

// Get files
$files = $fileService->getFiles('orders', $orderId);

// Delete file
$fileService->deleteFile($fileId);

// Delete semua file suatu record
$fileService->deleteModuleFiles('orders', $orderId);
```

**JavaScript (Frontend):**

```javascript
// Initialize file upload component di container manapun
App.FileUpload.init({
    module: 'orders',         // nama modul
    moduleId: 123,            // ID record
    container: '#my-section', // CSS selector container
    canUpload: true,          // tampilkan form upload
    canDelete: true,          // tampilkan tombol hapus
    multiple: true            // allow multiple files
});
```

### Contoh Implementasi

Lihat **Kelola Ekspedisi** sebagai contoh lengkap:
- Klik tombol **paperclip** pada ekspedisi untuk membuka modal file attachment
- Upload, preview thumbnail, download original, hapus file

### Tipe File yang Diizinkan

jpg, jpeg, png, gif, webp, pdf, doc, docx, xls, xlsx, csv, txt, zip, rar

Maksimal 5MB per file.

## Cara Kerja Permission

### Tabel Database

- **`modules`** - Menyimpan daftar menu/modul (nama, slug, icon, URL, urutan)
- **`role_permissions`** - Permission per role per modul (7 boolean: can_view, can_add, can_edit, can_delete, can_view_detail, can_upload, can_download)

### Flow

1. User login → permissions di-load ke session
2. Sidebar di-render dinamis dari session (hanya modul dengan `can_view=1`)
3. Setiap aksi di controller dicek dengan `checkPermission('module-slug', 'can_xxx')`
4. Jika tidak punya permission → halaman 403 Forbidden

### Manage Permission (Admin)

1. Login sebagai **admin**
2. Klik menu **Kelola Permission**
3. Pilih tab role (Admin/CS)
4. Toggle checkbox permission per modul
5. Klik **Simpan**
6. User dengan role tersebut perlu **logout & login ulang** untuk menerapkan perubahan (kecuali role sendiri, otomatis reload)

### Menambah Menu/Modul Baru

1. Klik menu **Kelola Menu / Modul**
2. Isi form: Nama, Slug (huruf kecil, unik), URL, pilih Icon
3. Klik **Simpan Modul**
4. Modul otomatis aktif untuk Admin, atur permission CS di halaman Permission

## Error Pages

- **404 Not Found** - Ditampilkan saat mengakses URL yang tidak ada
- **403 Forbidden** - Ditampilkan saat tidak punya permission untuk suatu halaman/aksi

## Flow Aplikasi (sesuai Flowchart)

### CS (Customer Service)
```
Login → Input Data Customer → List Order
         ↓ Edit Data → Cek sudah diexport? → Belum → Update
                                             → Sudah → BLOKIR
         ↓ Delete Data → Cek sudah diexport? → Belum → Delete
                                              → Sudah → BLOKIR
```

### Admin
```
Login → List Order → Pilih Ekspedisi → Export (CSV download)
        → Order yang diexport otomatis TERKUNCI
```

## Tech Stack

- **Backend**: PHP 7.4+ (MVC, PSR-4 Namespace, Controller/Service/Repository)
- **Database**: MySQL / MariaDB
- **Storage**: Local filesystem / MinIO (S3-compatible)
- **Frontend**: AdminLTE 3, Bootstrap 4, jQuery 3.6
- **Library JS**: DataTables, Select2, SweetAlert2, Toastr
- **Package Manager**: Composer
- **Environment**: vlucas/phpdotenv
