# QR Pay Backend API

Backend API (Headless) berbasis **Laravel** untuk sistem pembayaran menggunakan pemindaian QR Code. Aplikasi ini bertugas menghasilkan tautan pembayaran (Payment URL) dan menangani Webhook/Callback pembayaran terintegrasi dengan **Midtrans Payment Gateway**.

## 🛠️ Tech Stack
- **Framework:** Laravel 10/11 (PHP 8.3+)
- **Database:** MySQL / PostgreSQL
- **Gateway:** Midtrans (Snap API)
- **Queue/Cache:** Redis (Opsional)

## ⚙️ Persyaratan Sistem
Pastikan *environment* server/lokal Anda memiliki:
- PHP >= 8.2 (Direkomendasikan PHP 8.3)
- Composer
- MySQL / MariaDB

## 🚀 Instalasi & Setup Lokal

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/hariz-indratama/qr-banckend.git
   cd qr-banckend
   ```

2. **Install Dependensi:**
   ```bash
   composer install
   ```

3. **Pengaturan Environment (.env):**
   Salin file konfigurasi bawaan.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Lalu, buka file `.env` dan konfigurasikan database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=qr_pay
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Konfigurasi Midtrans:**
   Tambahkan *Server Key* Sandbox/Production Anda dari Dashboard Midtrans ke dalam file `.env`:
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXX
   MIDTRANS_IS_PRODUCTION=false
   ```

5. **Migrasi & Seeding Database:**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Aplikasi:**
   ```bash
   php artisan serve
   ```
   API akan berjalan secara lokal di `http://127.0.0.1:8000`.

---

## 📡 Deployment ke VPS (Cloudflare Origin Rule)

Jika aplikasi ini berjalan berdampingan dengan aplikasi web server lain (misal Apache) di port 80 pada VPS yang sama, kami merekomendasikan penggunaan **Cloudflare Origin Rules**:

1. Atur Nginx (`sites-available`) untuk `listen` pada port alternatif (contoh: `8899`).
2. Buat A Record di Cloudflare: `qrapi` -> IP VPS (Proxied/Awan Oranye).
3. Buat aturan **Origin Rule** di Cloudflare: `If Hostname equals qrapi.attendancemss.site` -> `Rewrite Destination Port ke 8899`.
4. Jangan lupa *update* **Notification URL** di Dashboard Midtrans menjadi:
   `https://qrapi.attendancemss.site/api/v1/payment/midtrans-callback`

## 🔒 Keamanan
Semua notifikasi asinkron (Webhook) dari Midtrans ke sistem ini divalidasi secara ketat menggunakan *Signature Key* (`SHA512(order_id + status_code + gross_amount + server_key)`) untuk mencegah pemalsuan transaksi.
