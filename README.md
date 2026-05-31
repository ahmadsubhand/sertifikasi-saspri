# 🚀 Setup Proyek dengan Laragon

Panduan ini berisi langkah-langkah untuk menjalankan aplikasi **Sertifikasi SASPRI** secara lokal menggunakan **Laragon**.

Setelah proses instalasi selesai, aplikasi dapat diakses melalui:

* **Frontend:** http://frontend.test
* **Backend:** http://backend.test

---

# 📋 Prasyarat

Pastikan perangkat Anda telah terinstal:

* Laragon (beserta PHP, MySQL, dan Apache/Nginx)
* Composer
* Git

---

# 🛠️ Langkah-langkah Instalasi

## 1. Clone Repository

Buka terminal Laragon, kemudian jalankan:

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

Melalui HeidiSQL, phpMyAdmin, atau tool database lainnya, buat database baru, misalnya:

```text
saspri_db
```

### Sesuaikan Konfigurasi Database

Buka file:

```text
common/config/main-local.php
```

Lalu sesuaikan konfigurasi dsn, username, dan password berikut:

```php
'db' => [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=saspri_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
],
```

---

## 5. Migrasi dan Seeding Database

Jalankan perintah berikut untuk membuat seluruh tabel dan mengisi data awal:

```bash
php yii migrate
php yii db/seed
```

### ⚠️ Informasi Akun Login Default

Semua akun yang dibuat melalui proses seeding menggunakan password yang sama:

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

Jalankan Command Prompt sebagai **Administrator**, lalu:

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

# 📝 Catatan

Jika terjadi perubahan konfigurasi:

* Jalankan ulang migration bila diperlukan.
* Pastikan Apache/Nginx dan MySQL pada Laragon sedang berjalan.
* Periksa kembali konfigurasi database pada `common/config/main-local.php`.
* Pastikan symbolic link mengarah ke folder `frontend/web` dan `backend/web` yang benar.