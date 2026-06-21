# 🚀 Deployment Sertifikasi SASPRI (Podman + Nginx)

Panduan ini menjelaskan proses deployment aplikasi **Sertifikasi SASPRI** pada server yang menggunakan **Podman**, **Nginx Reverse Proxy**, dan **MySQL**.

---

# 📂 Struktur Direktori

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

# 1. Setup Container Podman

## 1.1 Menambahkan Volume Mount

Volume mount digunakan untuk menghubungkan folder aplikasi pada VPS ke dalam container.

| Host VPS                                   | Container                          |
| ------------------------------------------ | ---------------------------------- |
| `/srv/podman/apps/php/sertifikasi-saspri`  | `/var/www/html/sertifikasi-saspri` |
| `/srv/podman/data/apps/sertifikasi-saspri` | `/var/www/data/sertifikasi-saspri` |

Edit file:

```bash
cd /srv/podman
sudo nano podman-compose.yml
```

Tambahkan konfigurasi berikut pada service `nginx` dan `php83_fpm`:

```yaml
nginx:
  volumes:
    #----------- MATASAPI ---------- #
    - ./apps/php/matasapi:/var/www/html/matasapi
    - ./data/apps/matasapi:/var/www/data/matasapi

    #----------- SERTIFIKASI SASPRI ---------- #
    - ./apps/php/sertifikasi-saspri:/var/www/html/sertifikasi-saspri
    - ./data/apps/sertifikasi-saspri:/var/www/data/sertifikasi-saspri

php83_fpm:
  volumes:
    #----------- MATASAPI ---------- #
    - ./apps/php/matasapi:/var/www/html/matasapi
    - ./data/apps/matasapi:/var/www/data/matasapi

    #----------- SERTIFIKASI SASPRI ---------- #
    - ./apps/php/sertifikasi-saspri:/var/www/html/sertifikasi-saspri
    - ./data/apps/sertifikasi-saspri:/var/www/data/sertifikasi-saspri
```

## 1.2 Restart Container

```bash
cd /srv/podman
sudo podman-compose up -d
```

---

# 2. Setup Database MySQL

## 2.1 Cek Password Root

```bash
cat /srv/podman/.env
```

Cari nilai:

```env
MYSQL_ROOT_PASSWORD=...
```

## 2.2 Masuk ke MySQL

```bash
sudo podman exec -it mysql mysql -u root -p
```

## 2.3 Membuat Database

```sql
CREATE DATABASE sertifikasi_saspri
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Keluar dari MySQL:

```sql
EXIT;
```

---

# 3. Setup Aplikasi Yii2

## 3.1 Masuk ke Direktori Aplikasi

```bash
cd /srv/podman/apps/php/sertifikasi-saspri
```

## 3.2 Clone Repository

```bash
git init
git remote add origin https://gitlab.com/ahmadsubhand/sertifikasi-saspri.git
git pull origin main
```

## 3.3 Install Dependency Composer

Jalankan Composer menggunakan container sementara:

```bash
sudo podman run --rm \
-v $(pwd):/app \
-w /app \
docker.io/library/composer \
install --ignore-platform-reqs
```

## 3.4 Inisialisasi Yii2

```bash
sudo podman exec -it php83_fpm \
bash -c "cd /var/www/html/sertifikasi-saspri && php init"
```

Pilih environment sesuai kebutuhan:

```text
0 -> Development Environment
yes
```

atau

```text
1 -> Production Environment
yes
```

## 3.5 Konfigurasi Database

Edit file:

```text
common/config/main-local.php
```

Sesuaikan konfigurasi database:

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

## 3.6 Menjalankan Migrasi

```bash
sudo podman exec -it php83_fpm \
bash -c "cd /var/www/html/sertifikasi-saspri && php yii migrate"
```

## 3.7 Menjalankan Seeder (Opsional)

Seeder hanya diperlukan untuk lingkungan pengembangan atau apabila membutuhkan data awal.

```bash
sudo podman exec -it php83_fpm \
bash -c "cd /var/www/html/sertifikasi-saspri && php yii db/seed"
```

---

# 4. Setup Akses Publik

## 4.1 Membuat Konfigurasi Nginx

Masuk ke direktori:

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

## 4.2 Mengaktifkan Site

```bash
sudo ln -s \
/srv/podman/infra/nginx/sites-available/site-sertifikasi.conf \
/srv/podman/infra/nginx/sites-enabled/
```

## 4.3 Reload Nginx

```bash
sudo podman exec -it nginx_reverse_proxy nginx -s reload
```

---

# 5. Setup SSL Certificate

Pastikan domain telah mengarah ke server sebelum menjalankan langkah berikut.

## 5.1 Generate Sertifikat Baru

```bash
sudo certbot certonly \
--webroot \
-w /srv/podman/apps/php/sertifikasi-saspri/frontend/web \
-d sertifikasi.digdaya.net
```

## 5.2 Pasang Sertifikat Baru

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

## 5.3 Validasi dan Reload Nginx

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

Apabila folder `sertifikasi-saspri` belum muncul, berarti volume mount belum didaftarkan pada `podman-compose.yml`.

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

Jika folder `sertifikasi-saspri` tidak ada, maka container belum dapat mengakses aplikasi tersebut.

---

## Mengetahui Konfigurasi Nginx

Selama pengembangan lokal menggunakan Laragon, virtual host diatur secara otomatis melalui folder `sites-enabled`.

Pada server Podman, konsep yang digunakan sama:

```text
/srv/podman/infra/nginx/sites-enabled/
```

Konfigurasi aplikasi lain seperti `site-matasapi.conf` dapat digunakan sebagai referensi.

---

## Mengetahui Konfigurasi SSL

Periksa konfigurasi Nginx yang sudah ada:

```nginx
ssl_certificate ...
ssl_certificate_key ...
```

atau lihat daftar sertifikat:

```text
/etc/letsencrypt/live/
```

Dari konfigurasi tersebut dapat diketahui bahwa SSL dikelola menggunakan **Certbot (Let's Encrypt)**.

---

# 📝 Catatan

* Container tidak mengakses path fisik VPS secara langsung, melainkan path virtual yang didaftarkan melalui volume mount. -=-
* Pastikan database telah dibuat sebelum menjalankan migration. -=-
* Jalankan migration kembali apabila terdapat perubahan struktur database. -=-
* Seeder hanya digunakan untuk kebutuhan data awal atau lingkungan pengembangan. -=-
* Pastikan domain telah mengarah ke server sebelum menjalankan Certbot. -=-
* Gunakan konfigurasi aplikasi lain yang sudah berjalan sebagai referensi saat membuat konfigurasi Nginx baru.
* Selalu lakukan validasi konfigurasi Nginx menggunakan `nginx -t` sebelum reload.
* Pastikan volume mount sudah aktif sebelum menjalankan perintah Yii2 seperti `php init`, `php yii migrate`, atau `php yii db/seed`.
