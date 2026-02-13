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
- **UI Modern** - AdminLTE 3, DataTables, Select2, SweetAlert2, Toastr

## Requirement

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Apache dengan mod_rewrite (atau nginx)

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

### 4. Konfigurasi Database

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');         // sesuaikan password
define('DB_NAME', 'order_management');
```

### 5. Konfigurasi Web Server

**Apache (XAMPP/Laragon):**
- Pastikan `mod_rewrite` aktif
- Arahkan DocumentRoot ke folder project, atau akses via `http://localhost/order-management/`

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

### 6. Akses Aplikasi

Buka browser: `http://localhost/order-management/`

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
│   │   ├── PermissionController.php
│   │   └── ModuleController.php
│   └── Models/
│       ├── User.php
│       ├── Order.php
│       ├── Expedition.php
│       └── Permission.php
├── config/
│   ├── app.php              # Helper functions & session
│   └── database.php         # Koneksi database
├── views/
│   ├── layouts/
│   │   ├── header.php       # HTML head + CSS
│   │   ├── navbar.php       # Top navigation bar
│   │   ├── sidebar.php      # Dynamic sidebar menu
│   │   └── footer.php       # JS + global flash handler
│   ├── auth/login.php
│   ├── dashboard/index.php
│   ├── orders/
│   │   ├── index.php        # List order + filter
│   │   ├── create.php       # Form input customer
│   │   └── edit.php         # Form edit order
│   ├── admin/index.php      # Export order per ekspedisi
│   ├── expeditions/index.php
│   ├── permissions/index.php # Matrix permission per role
│   └── modules/index.php    # Kelola menu + icon picker
├── vendor/                  # Composer autoload (auto-generated)
├── database.sql             # SQL schema + seed data
├── composer.json            # PSR-4 autoload config
├── .htaccess                # URL rewriting
├── .env.example             # Template environment
├── .gitignore
├── index.php                # Router utama
└── README.md
```

## Cara Kerja Permission

### Tabel Database

- **`modules`** - Menyimpan daftar menu/modul (nama, slug, icon, URL, urutan)
- **`role_permissions`** - Permission per role per modul (7 boolean: can_view, can_add, can_edit, can_delete, can_view_detail, can_upload, can_download)

### Flow

1. User login → permissions di-load ke session
2. Sidebar di-render dinamis dari session (hanya modul dengan `can_view=1`)
3. Setiap aksi di controller dicek dengan `checkPermission('module-slug', 'can_xxx')`
4. Jika tidak punya permission → redirect ke dashboard + pesan error

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

## Flow Aplikasi (sesuai Flowchart)

### CS (Customer Service)
```
Login → Input Data Customer → List Order
         ↓ Edit Data → Cek sudah diexport? → Belum → Update ✓
                                             → Sudah → BLOKIR ✗
         ↓ Delete Data → Cek sudah diexport? → Belum → Delete ✓
                                              → Sudah → BLOKIR ✗
```

### Admin
```
Login → List Order → Pilih Ekspedisi → Export (CSV download)
        → Order yang diexport otomatis TERKUNCI
```

## Tech Stack

- **Backend**: PHP 7.4+ (MVC, PSR-4 Namespace)
- **Database**: MySQL / MariaDB
- **Frontend**: AdminLTE 3, Bootstrap 4, jQuery 3.6
- **Library JS**: DataTables, Select2, SweetAlert2, Toastr
- **Package Manager**: Composer
