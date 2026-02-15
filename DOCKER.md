# Docker Installation Guide

## Prasyarat

- [Docker](https://docs.docker.com/get-docker/) >= 20.10
- [Docker Compose](https://docs.docker.com/compose/install/) >= 2.0

## Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/apeengggg/order-manage.git
cd order-manage
```

### 2. Konfigurasi Environment

```bash
cp .env.docker .env
```

Edit `.env` jika perlu mengubah port atau password:

| Variable | Default | Keterangan |
|----------|---------|------------|
| `APP_PORT` | `8080` | Port aplikasi web |
| `DB_PORT` | `3306` | Port MySQL (external) |
| `PMA_PORT` | `8081` | Port phpMyAdmin |
| `DB_USER` | `order_user` | Username database |
| `DB_PASS` | `order_password` | Password database |
| `DB_ROOT_PASS` | `root_password` | Password root MySQL |
| `DB_NAME` | `order_management` | Nama database |
| `STORAGE_DRIVER` | `local` | Storage driver (`local` / `minio`) |
| `MINIO_API_PORT` | `9000` | Port MinIO API |
| `MINIO_CONSOLE_PORT` | `9001` | Port MinIO Console |
| `MINIO_ACCESS_KEY` | `minioadmin` | MinIO access key |
| `MINIO_SECRET_KEY` | `minioadmin` | MinIO secret key |
| `MINIO_BUCKET` | `uploads` | Nama bucket MinIO |

### 3. Build & Jalankan

```bash
docker compose up -d --build
```

Tunggu hingga semua container running:

```bash
docker compose ps
```

Output yang diharapkan:

```
NAME                STATUS                    PORTS
order-app           Up                        0.0.0.0:8080->80/tcp
order-db            Up (healthy)              0.0.0.0:3306->3306/tcp
order-minio         Up (healthy)              0.0.0.0:9000->9000/tcp, 0.0.0.0:9001->9001/tcp
order-minio-setup   Exited (0)                (one-time setup, normal)
order-pma           Up                        0.0.0.0:8081->80/tcp
```

> `order-minio-setup` akan exit setelah membuat bucket. Ini normal.

### 4. Akses Aplikasi

| Service | URL | Keterangan |
|---------|-----|------------|
| Aplikasi | http://localhost:8080 | Order Management System |
| phpMyAdmin | http://localhost:8081 | Database management |
| MinIO Console | http://localhost:9001 | Object storage management |

### 5. Login Default

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Admin (full access) |
| `cs1` | `admin123` | Customer Service |
| `cs2` | `admin123` | Customer Service |

> **Penting:** Segera ganti password default setelah login pertama kali.

---

## Data Persistence (Volume)

Semua data penting disimpan di Docker named volumes agar tidak hilang saat container di-restart atau di-rebuild:

| Volume | Isi | Keterangan |
|--------|-----|------------|
| `order-db-data` | MySQL database | Semua data order, user, ekspedisi |
| `order-storage-data` | File uploads | Foto, template XLSX, attachment |
| `order-vendor-data` | Composer packages | PHP dependencies |
| `order-minio-data` | MinIO objects | File jika pakai MinIO storage |

### Melihat volume

```bash
docker volume ls | grep order
```

### Data TIDAK hilang saat:
- `docker compose down` (stop & hapus container)
- `docker compose up -d --build` (rebuild image)
- `docker compose restart` (restart container)

### Data HILANG saat:
- `docker compose down -v` (hapus container **DAN** volume)
- `docker volume rm order-db-data` (hapus volume manual)

---

## Storage Driver: Local vs MinIO

### Mode Local (Default)

File disimpan di filesystem container via volume `order-storage-data`.

```env
STORAGE_DRIVER=local
```

### Mode MinIO (Object Storage)

File disimpan di MinIO S3-compatible object storage. Cocok untuk production dan multi-server.

1. Edit `.env`:

```env
STORAGE_DRIVER=minio
MINIO_ENDPOINT=minio:9000
MINIO_BUCKET=uploads
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
```

2. Restart app:

```bash
docker compose restart app
```

3. Akses MinIO Console di http://localhost:9001
   - Login: `minioadmin` / `minioadmin`
   - Bucket `uploads` otomatis dibuat oleh `minio-setup`

---

## Perintah Umum

### Menjalankan

```bash
docker compose up -d
```

### Menghentikan

```bash
docker compose down
```

### Menghentikan + Hapus Semua Data

```bash
docker compose down -v
```

> **Peringatan:** Perintah ini menghapus semua data (database, uploads, minio).

### Melihat Log

```bash
# Semua service
docker compose logs -f

# Hanya aplikasi
docker compose logs -f app

# Hanya database
docker compose logs -f db

# Hanya MinIO
docker compose logs -f minio
```

### Rebuild Setelah Update Code

```bash
docker compose up -d --build
```

### Masuk ke Container

```bash
# PHP/Apache container
docker compose exec app bash

# MySQL container
docker compose exec db mysql -u order_user -porder_password order_management
```

### Install Composer Package Baru

```bash
docker compose exec app composer require nama/package
```

---

## Backup & Restore

### Backup Database

```bash
docker compose exec db mysqldump -u root -proot_password order_management > backup_$(date +%Y%m%d).sql
```

### Restore Database

```bash
docker compose exec -T db mysql -u root -proot_password order_management < backup.sql
```

### Backup Storage Files

```bash
# Copy dari volume ke host
docker compose cp app:/var/www/html/storage/uploads ./backup-uploads
```

### Restore Storage Files

```bash
docker compose cp ./backup-uploads/. app:/var/www/html/storage/uploads/
docker compose exec app chown -R www-data:www-data /var/www/html/storage
```

---

## Struktur Docker

```
order-manage/
├── docker-compose.yml       # Definisi semua services
├── Dockerfile               # PHP 8.2 + Apache + extensions
├── .dockerignore            # File yang di-exclude dari build
├── .env.docker              # Template environment untuk Docker
└── docker/
    └── init.sql             # Schema database untuk fresh install
```

### Services

| Service | Image | Port | Keterangan |
|---------|-------|------|------------|
| `app` | PHP 8.2 + Apache | 8080 | Aplikasi web |
| `db` | MySQL 8.0 | 3306 | Database dengan healthcheck |
| `minio` | MinIO | 9000, 9001 | Object storage (S3-compatible) |
| `minio-setup` | MinIO Client | - | Auto-create bucket (one-time) |
| `phpmyadmin` | phpMyAdmin 5 | 8081 | Database GUI |

---

## Troubleshooting

### Container `app` tidak bisa connect ke database

Database butuh waktu inisialisasi (~30 detik). Cek status:

```bash
docker compose ps
docker compose logs -f db
```

Tunggu hingga `db` menunjukkan `ready for connections`.

### Port sudah digunakan

Edit `.env` dan ubah port:

```env
APP_PORT=8888
DB_PORT=3307
PMA_PORT=8082
MINIO_API_PORT=9002
MINIO_CONSOLE_PORT=9003
```

Lalu restart:

```bash
docker compose down && docker compose up -d
```

### Reset database dari awal

```bash
docker compose down -v
docker compose up -d
```

### Permission error pada storage

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/storage
```

### Melihat error PHP

```bash
docker compose exec app tail -f /var/log/apache2/error.log
```

### MinIO bucket tidak terbuat

Jalankan setup ulang:

```bash
docker compose run --rm minio-setup
```

---

## Production Deployment

Untuk production, disarankan:

1. **Ganti semua password default** di `.env`
2. **Hapus phpMyAdmin** atau batasi akses (jangan expose ke public)
3. **Gunakan HTTPS** via reverse proxy (nginx/traefik di depan):

```yaml
# Contoh tambahan di docker-compose.yml
  nginx:
    image: nginx:alpine
    ports:
      - "443:443"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt
    depends_on:
      - app
```

4. **Jangan expose port DB & MinIO** ke external:

```env
DB_PORT=
MINIO_API_PORT=
MINIO_CONSOLE_PORT=
PMA_PORT=
```

5. **Backup otomatis** via cron:

```bash
# Tambahkan ke crontab host
0 2 * * * docker compose -f /path/to/docker-compose.yml exec -T db mysqldump -u root -proot_password order_management | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz
```
