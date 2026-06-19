# 🚀 Sertifikasi SASPRI

Aplikasi Sertifikasi SASPRI berbasis Yii2 yang terdiri dari frontend dan backend.

Dokumen ini mencakup:

1. Setup lingkungan pengembangan lokal menggunakan Laragon.
2. Deployment aplikasi ke server menggunakan Podman, Nginx, dan Let's Encrypt SSL.
3. Troubleshooting yang umum ditemui selama proses deployment.

---

# 📋 Prasyarat

## Untuk Pengembangan Lokal

Pastikan perangkat Anda telah terinstal:

* Laragon (PHP, MySQL, Apache/Nginx)
* Composer
* Git

## Untuk Deployment Server

Pastikan server telah memiliki:

* Podman
* Podman Compose
* MySQL Container
* Nginx Reverse Proxy Container
* Git
* Certbot
* Domain yang sudah mengarah ke alamat IP server

---

# 🛠️ Setup Lokal dengan Laragon

## 1. Clone Repository

Buka terminal Laragon kemudian jalankan:

```bash
git clone <URL_REPOSITORY> nama-folder-proyek
cd nama-folder-proyek
```

> Ganti `<URL_REPOSITORY>` dengan URL repository proyek.

---

## 2. Install Dependency

Install seluruh dependency yang dibutuhkan aplikasi:

```bash
composer install
```

---

## 3. Inisialisasi Proyek Yii2

Jalankan perintah berikut:

```bash
php init
```

Kemudian pilih:

* `0` → Development Environment
* `yes` → Konfirmasi inisialisasi

Perintah ini akan membuat file konfigurasi lokal seperti:

* `common/config/main-local.php`
* `frontend/config/main-local.php`
* `backend/config/main-local.php`

---

## 4. Konfigurasi Database

### Buat Database

Melalui HeidiSQL, phpMyAdmin, atau tool database lainnya, buat database baru:

```sql
CREATE DATABASE sertifikasi_saspri;
```

### Sesuaikan Konfigurasi Database

Buka file:

```text
common/config/main-local.php
```

Lalu sesuaikan konfigurasi berikut:

```php
'db' => [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=sertifikasi_saspri',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
],
```

---

## 5. Migrasi dan Seeding Database

Jalankan perintah berikut:

```bash
php yii migrate
php yii db/seed
```

### Informasi Akun Login Default

Semua akun yang dibuat melalui proses seeding menggunakan password:

```text
password_0
```

Password tersebut berlaku untuk seluruh role yang tersedia, seperti:

* Admin
* Wali
* Anggota

---

## 6. Setup Virtual Host Laragon

Agar aplikasi dapat diakses menggunakan domain `.test`, buat symbolic link dari folder `web` Yii2 ke folder `www` milik Laragon.

### Frontend

Jalankan Command Prompt sebagai Administrator:

```cmd
mklink /D "C:\laragon\www\frontend" "C:\path\ke\proyek\frontend\web"
```

### Backend

```cmd
mklink /D "C:\laragon\www\backend" "C:\path\ke\proyek\backend\web"
```

### Contoh

Jika proyek berada di:

```text
D:\Projects\saspri-app
```

Maka perintahnya menjadi:

```cmd
mklink /D "C:\laragon\www\frontend" "D:\Projects\saspri-app\frontend\web"

mklink /D "C:\laragon\www\backend" "D:\Projects\saspri-app\backend\web"
```

---

## 7. Restart Laragon

Pastikan Laragon dalam kondisi aktif.

Jika sebelumnya sudah berjalan:

1. Klik **Stop All**
2. Klik **Start All**

Laragon akan secara otomatis:

* Mendeteksi folder baru
* Membuat virtual host
* Mengatur file hosts
* Mengaktifkan domain `.test`

---

# 🎉 Menjalankan Aplikasi

Setelah seluruh langkah selesai, buka browser dan akses:

## Frontend

```text
http://frontend.test
```

## Backend

```text
http://backend.test
```

Gunakan akun hasil seeding dengan password:

```text
password_0
```

---

# 🚀 Deployment ke Server (Podman + Nginx)

## Struktur Direktori

```text
/srv/podman
├── apps/
│   └── php/
├── data/
│   └── apps/
└── infra/
    └── nginx/
```

---

## 1. Setup Volume Mount

Edit file:

```bash
cd /srv/podman
sudo nano podman-compose.yml
```

Tambahkan volume berikut pada service `nginx` dan `php83_fpm`:

```yaml
- ./apps/php/sertifikasi-saspri:/var/www/html/sertifikasi-saspri
- ./data/apps/sertifikasi-saspri:/var/www/data/sertifikasi-saspri
```

Pemetaan direktori:

| Host VPS                                   | Container                          |
| ------------------------------------------ | ---------------------------------- |
| `/srv/podman/apps/php/sertifikasi-saspri`  | `/var/www/html/sertifikasi-saspri` |
| `/srv/podman/data/apps/sertifikasi-saspri` | `/var/www/data/sertifikasi-saspri` |

---

## 2. Restart Container

```bash
cd /srv/podman
sudo podman-compose up -d
```

---

## 3. Setup Database

### Cek Password Root MySQL

```bash
cat /srv/podman/.env
```

Cari nilai:

```env
MYSQL_ROOT_PASSWORD=...
```

### Masuk ke MySQL

```bash
sudo podman exec -it mysql mysql -u root -p
```

### Buat Database

```sql
CREATE DATABASE sertifikasi_saspri
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

---

## 4. Clone Repository

```bash
cd /srv/podman/apps/php

mkdir -p sertifikasi-saspri
cd sertifikasi-saspri

git init
git remote add origin https://gitlab.com/ahmadsubhand/sertifikasi-saspri.git
git pull origin main
```

---

## 5. Install Dependency

Jalankan Composer menggunakan container sementara:

```bash
sudo podman run --rm \
  -v $(pwd):/app \
  -w /app \
  docker.io/library/composer \
  install --ignore-platform-reqs
```

---

## 6. Inisialisasi Yii2

```bash
sudo podman exec -it php83_fpm \
bash -c "cd /var/www/html/sertifikasi-saspri && php init"
```

Pilih:

```text
0 -> Development Environment
yes -> Konfirmasi
```

atau

```text
1 -> Production Environment
yes -> Konfirmasi
```

---

## 7. Konfigurasi Database

Edit file:

```text
common/config/main-local.php
```

Sesuaikan konfigurasi:

```php
'db' => [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=mysql;dbname=sertifikasi_saspri',
    'username' => 'root',
    'password' => 'MYSQL_ROOT_PASSWORD',
    'charset' => 'utf8mb4',
],
```

---

## 8. Migrasi dan Seeder

```bash
sudo podman exec -it php83_fpm \
bash -c "cd /var/www/html/sertifikasi-saspri && php yii migrate"
```

```bash
sudo podman exec -it php83_fpm \
bash -c "cd /var/www/html/sertifikasi-saspri && php yii db/seed"
```

> Seeder hanya diperlukan pada lingkungan pengembangan atau saat membutuhkan data awal.

---

## 9. Setup Nginx

Masuk ke direktori konfigurasi:

```bash
cd /srv/podman/infra/nginx/sites-available
```

Buat file:

```bash
sudo nano site-sertifikasi.conf
```

Salin konfigurasi Nginx sesuai kebutuhan aplikasi.

---

## 10. Aktifkan Site

```bash
sudo ln -s \
/srv/podman/infra/nginx/sites-available/site-sertifikasi.conf \
/srv/podman/infra/nginx/sites-enabled/
```

---

## 11. Reload Nginx

```bash
sudo podman exec -it nginx_reverse_proxy nginx -s reload
```

---

## 12. Generate SSL Certificate

Pastikan domain telah mengarah ke server.

```bash
sudo certbot certonly \
--webroot \
-w /srv/podman/apps/php/sertifikasi-saspri/frontend/web \
-d sertifikasi.digdaya.net
```

---

## 13. Pasang SSL Certificate

Perbarui konfigurasi Nginx:

```nginx
ssl_certificate      /etc/letsencrypt/live/sertifikasi.digdaya.net/fullchain.pem;
ssl_certificate_key  /etc/letsencrypt/live/sertifikasi.digdaya.net/privkey.pem;
```

---

## 14. Validasi dan Reload Nginx

Validasi konfigurasi:

```bash
sudo podman exec -it nginx_reverse_proxy nginx -t
```

Reload:

```bash
sudo podman exec -it nginx_reverse_proxy nginx -s reload
```

---

# 🔍 Troubleshooting

## Melihat Daftar Container

```bash
sudo podman ps -a
```

---

## Memeriksa Volume Mount

```bash
sudo podman inspect \
-f '{{range .Mounts}}Dari VPS: {{.Source}} ---> Ke Container: {{.Destination}}{{"\n"}}{{end}}' \
php83_fpm
```

---

## Memastikan Folder Aplikasi Terbaca di Container

Periksa direktori kerja container:

```bash
sudo podman exec -it php83_fpm pwd
```

Contoh output:

```text
/var/www/html
```

Lihat isi direktori:

```bash
sudo podman exec -it php83_fpm ls -l /var/www/html
```

Jika folder `sertifikasi-saspri` belum muncul, kemungkinan volume mount belum terdaftar pada `podman-compose.yml`.

---

## Mengetahui Konfigurasi Nginx yang Digunakan

Daftar site aktif:

```text
/srv/podman/infra/nginx/sites-enabled/
```

Gunakan konfigurasi aplikasi lain sebagai referensi saat membuat virtual host baru.

---

## Mengetahui Konfigurasi SSL

Periksa sertifikat yang tersedia:

```text
/etc/letsencrypt/live/
```

Atau lihat konfigurasi:

```nginx
ssl_certificate ...
ssl_certificate_key ...
```

Konfigurasi tersebut menunjukkan bahwa SSL dikelola menggunakan Certbot (Let's Encrypt).

---

# 📝 Catatan

* Jalankan ulang migration bila diperlukan.
* Pastikan database sudah dibuat sebelum menjalankan migration.
* Pastikan Apache/Nginx dan MySQL berjalan pada lingkungan yang digunakan.
* Periksa kembali konfigurasi database pada `common/config/main-local.php`.
* Pastikan symbolic link Laragon mengarah ke folder yang benar.
* Pastikan volume mount Podman telah terpasang dengan benar sebelum menjalankan perintah Yii2.
* Pastikan domain sudah mengarah ke server sebelum melakukan generate SSL menggunakan Certbot.
