# SIPN - Sistem Penilaian Siswa

SIPN adalah aplikasi berbasis PHP dan MySQL untuk pengelolaan akun, data siswa, data guru, mata pelajaran, penugasan mengajar, dan nilai rapor.

## Fitur

- Autentikasi dan otorisasi berdasarkan peran: admin, guru, dan siswa
- Pengelolaan akun, profil, siswa, guru, dan mata pelajaran
- Pengelolaan relasi guru-mata pelajaran dan mengajar
- Pencatatan nilai pengetahuan dan keterampilan
- Pencarian data melalui antarmuka web
- Logout dan perlindungan sesi

## Teknologi

- PHP 8.1+
- MySQL / MariaDB
- mysqli
- Tailwind CSS
- Flowbite

## Struktur Direktori

```text
tugasbesar/
├── assets/
│   ├── css/                 # Berkas CSS
│   └── images/              # Logo dan ilustrasi
├── database/
│   └── schema.sql           # Struktur dan data awal basis data
├── auth.php                 # Session dan kontrol akses
├── kon.php                  # Koneksi MySQL
├── login.php                # Halaman login
├── register.php             # Pendaftaran akun
├── home_admin.php           # Dashboard admin
├── beranda.php              # Dashboard guru dan siswa
├── navbar.php               # Navigasi bersama
└── *.php                    # Modul aplikasi
```

## Persyaratan

- Laragon atau lingkungan lokal PHP/MySQL
- PHP 8.1 atau lebih baru
- MySQL 8.0 atau MariaDB yang kompatibel
- Ekstensi PHP `mysqli`
- Web browser modern

## Instalasi

1. Clone repository ke direktori web Laragon:

   ```bash
   git clone https://github.com/Euryan/Tugas_Besar_Eureka_Noor_Royan_Abdurrahman_Akram.git
   cd tugasbesar
   ```

2. Jalankan Apache dan MySQL melalui Laragon.

3. Buat basis data yang sesuai dan import dump:

   ```sql
   CREATE DATABASE tugas_besar_xis1;
   ```

   Import `database/schema.sql` melalui phpMyAdmin atau terminal:

   ```bash
   mysql -u root -p tugas_besar_xis1 < database/schema.sql
   ```

4. Sesuaikan konfigurasi database pada `kon.php` bila diperlukan:

   ```php
   $username = "root";
   $password = "";
   $hostname = "localhost";
   $db = "tugas_besar_xis1";
   ```

5. Buka aplikasi di browser, misalnya:

   ```text
   http://tugasbesar.test
   ```

## Menjalankan Pengembangan

Pastikan folder proyek dapat diakses oleh Laragon, lalu buka URL virtual host pada browser. Jika virtual host belum dibuat, tambahkan host baru melalui Laragon atau jalankan aplikasi dengan Apache Laragon.

## Catatan Basis Data

Dump SQL awal menggunakan nama database `xirpl1`, sedangkan konfigurasi aplikasi saat ini menggunakan `tugas_besar_xis1`. Buat atau gunakan database sesuai konfigurasi pada `kon.php`. Dump memuat data contoh dan harus disesuaikan sebelum dipakai untuk lingkungan produksi.

## Keamanan

- Gunakan password database yang kuat pada lingkungan produksi.
- Jangan gunakan data atau akun contoh untuk sistem produksi.
- Ganti seluruh password akun demo.
- Tambahkan lingkungan produksi di balik HTTPS.
- Simpan konfigurasi sensitif melalui variabel lingkungan atau konfigurasi yang tidak dibagikan ke repository.

## Kontributor

- Eureka Noor Royan Abdurrahman Akram
- Team Tugas Besar XIRPL1

## Lisensi

Belum ditentukan.
