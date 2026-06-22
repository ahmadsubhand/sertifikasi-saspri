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

## 8. Menjalankan Aplikasi

Setelah seluruh langkah selesai, buka browser dan akses:

Frontend

```text
http://frontend.test
```

Backend

```text
http://backend.test
```

---

# 🚀 Deployment ke Server (Podman + Nginx)

## Struktur Direktori

```text
/srv/podman
├── apps/
│   └── php/
│       └── sertifikasi-saspri/
├── data/
│   └── apps/
│       └── sertifikasi-saspri/
└── infra/
    └── nginx/
        ├── sites-available/
        └── sites-enabled/
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

Gunakan nilai `MYSQL_ROOT_PASSWORD` yang diperoleh pada tahap sebelumnya.

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

Isi dengan konfigurasi berikut.

### HTTP (Sementara untuk Certbot)

```nginx
server {
    listen 80;
    server_name sertifikasi.digdaya.net www.sertifikasi.digdaya.net;

    location /.well-known/acme-challenge/ {
        root /var/www/html/sertifikasi-saspri/frontend/web;
        allow all;
    }

    location / {
        return 301 https://$host$request_uri;
    }

    root /var/www/html/sertifikasi-saspri/frontend/web;
    index index.php index.html;

    access_log /var/log/nginx/sertifikasi_access.log;
    error_log  /var/log/nginx/sertifikasi_error.log warn;

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf)$ {
        expires 7d;
        access_log off;
        add_header Cache-Control "public, max-age=604800";
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### HTTPS

Gunakan sementara sertifikat utama agar Nginx dapat berjalan sebelum sertifikat domain baru dibuat.

```nginx
server {
    listen 443 ssl;
    http2 on;

    server_name sertifikasi.digdaya.net www.sertifikasi.digdaya.net;

    root /var/www/html/sertifikasi-saspri/frontend/web;
    index index.php index.html;

    charset utf-8;

    access_log /var/log/nginx/sertifikasi_access.log;
    error_log  /var/log/nginx/sertifikasi_error.log warn;

    include /etc/nginx/snippets/ssl-params.conf;

    ssl_certificate      /etc/letsencrypt/live/digdaya.net/fullchain.pem;
    ssl_certificate_key  /etc/letsencrypt/live/digdaya.net/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    location ~ ^/(assets|css|js|images|uploads|themes)/ {
        try_files $uri =404;
        expires 30d;
        access_log off;
        add_header Cache-Control "public, no-transform";
    }

    location ~ \.php$ {
        include /etc/nginx/snippets/fastcgi-common.conf;

        try_files $uri =404;

        fastcgi_param SERVER_PORT 443;
        fastcgi_param HTTP_HOST $host;
        fastcgi_param HTTPS on;
        fastcgi_param REQUEST_SCHEME https;

        fastcgi_pass php83_fpm_nginx;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
        expires 7d;
        access_log off;
    }

    location ~ /\.(ht|git|env|svn) {
        deny all;
    }
}
```

---

## 10. Aktifkan Site

Masuk ke direktori

```bash
cd /srv/podman/infra/nginx/sites-enabled
```

Buat symbolic link

```bash
sudo ln -s ../sites-available/site-sertifikasi.conf site-sertifikasi.conf
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

Edit kembali konfigurasi:

```bash
sudo nano /srv/podman/infra/nginx/sites-available/site-sertifikasi.conf
```

Ganti:

```nginx
ssl_certificate      /etc/letsencrypt/live/digdaya.net/fullchain.pem;
ssl_certificate_key  /etc/letsencrypt/live/digdaya.net/privkey.pem;
```

Menjadi:

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

# 👤 Informasi Akun Login Default

Semua akun yang dibuat melalui proses seeding menggunakan password:

```text
password_0
```

Password tersebut berlaku untuk seluruh role yang tersedia, seperti:

* Admin SASPRI-N (admin)
* Wali SASPRI-K (coordinator)
* Anggota SASPRI-K (user)

# 🔍 Troubleshooting

## Melihat Daftar Container

```bash
sudo podman ps -a
```

---

## Memeriksa Volume Mount

Lihat pemetaan folder VPS ke container:

```bash
sudo podman inspect \
-f '{{range .Mounts}}Dari VPS: {{.Source}} ---> Ke Container: {{.Destination}}{{"\n"}}{{end}}' \
php83_fpm
```

Contoh output:

```text
Dari VPS: /srv/podman/apps/php/matasapi ---> Ke Container: /var/www/html/matasapi
Dari VPS: /srv/podman/data/apps/ssmi-erp ---> Ke Container: /var/www/data/ssmi-erp
```

Jika folder `sertifikasi-saspri` belum muncul, kemungkinan volume mount belum terdaftar pada `podman-compose.yml`.

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

Contoh output:

```text
total 20
drwxr-xr-x  2 1007 1007 4096 May 19 00:36 asset-mgmt
drwxr-xr-x  3 1007 1007 4096 Jun  4 15:15 cassmatech
drwxr-xr-x  9 1007 1007 4096 May 19 00:53 matasapi
drwxr-xr-x 16 1007 1007 4096 May 19 00:53 portal
drwxr-xr-x  9 1007 1007 4096 May 19 00:07 ssmi-erp
```

Jika folder `sertifikasi-saspri` belum muncul, kemungkinan volume mount belum terdaftar pada `podman-compose.yml`.

---

## Mengetahui Konfigurasi Nginx yang Digunakan

Selama pengembangan lokal menggunakan Laragon, virtual host diatur secara otomatis melalui folder `sites-enabled`.

Pada server Podman, konsep yang digunakan sama:

```text
/srv/podman/infra/nginx/sites-enabled/
```

Konfigurasi aplikasi lain seperti `site-matasapi.conf` dapat digunakan sebagai referensi.

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

Konfigurasi tersebut menunjukkan bahwa SSL dikelola menggunakan **Certbot (Let's Encrypt)**.

---

# 📝 Catatan

## Tahap Pengembangan

* Pastikan symbolic link Laragon mengarah ke folder yang benar.

## Volume Mount dan Container

* Container tidak mengakses path fisik VPS secara langsung, melainkan path virtual yang didaftarkan melalui volume mount.
* Pastikan volume mount Podman telah terpasang dengan benar sebelum menjalankan perintah Yii2 seperti `php init`, `php yii migrate`, atau `php yii db/seed`.

## Database dan Yii2

* Pastikan database sudah dibuat sebelum menjalankan migration.
* Periksa kembali konfigurasi database pada `common/config/main-local.php`.
* Jalankan migration kembali apabila terdapat perubahan struktur database.
* Seeder hanya digunakan untuk kebutuhan data awal atau lingkungan pengembangan.

## Nginx

* Gunakan konfigurasi aplikasi lain yang sudah berjalan sebagai referensi saat membuat konfigurasi Nginx baru.
* Selalu lakukan validasi konfigurasi Nginx menggunakan `nginx -t` sebelum reload.

## SSL

* Pastikan domain sudah mengarah ke server sebelum melakukan generate SSL menggunakan Certbot.
