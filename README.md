# SIMAC — Sistem Manajemen Layanan Maintenance AC

**SIMAC** adalah aplikasi web untuk mengelola operasional bisnis jasa maintenance AC:
mulai dari pendataan customer & unit AC, pembuatan booking servis, penugasan dan
pelacakan status teknisi, pencatatan pembayaran & ulasan, hingga laporan untuk
pemilik/manajemen. Antarmukanya responsif — nyaman dipakai di desktop maupun HP.

Dibangun dengan Laravel 13 sesuai PRD v1.0 (Fakhri, 20 Agustus 2026).

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Peran Pengguna](#peran-pengguna)
- [Menjalankan dengan Docker](#menjalankan-dengan-docker)
- [Akun Demo](#akun-demo-setelah-seed)
- [Alur Booking](#alur-booking)
- [Struktur Kode](#struktur-kode-penting)
- [Pengembangan Lokal & Testing](#pengembangan-lokal-tanpa-docker)
- [Catatan Konvensi](#catatan-konvensi)

---

## Fitur Utama

### Operasional
- **Manajemen Customer & Unit AC** — data customer beserta daftar unit AC (merek,
  tipe, kapasitas PK, lokasi) untuk memudahkan teknisi di lapangan.
- **Booking Servis** — buat pesanan servis, pilih customer, unit AC, layanan, dan
  jadwal. Jika customer/unit belum terdaftar, bisa langsung didaftarkan dari halaman
  booking lalu **kembali otomatis dengan data terpilih** (alur mulus tanpa kehilangan konteks).
- **Penugasan & Status Teknisi** — assign teknisi yang *available*, pantau perpindahan
  status pekerjaan; setiap perubahan tercatat di riwayat (audit trail).
- **Pembayaran & Ulasan** — pencatatan pembayaran manual dan ulasan setelah servis selesai.
- **Pengingat servis berkala** — dashboard admin menandai unit AC yang servis terakhirnya
  lebih dari 90 hari lalu, lengkap dengan tombol untuk langsung menjadwalkan booking.
- **Notifikasi WhatsApp** — tombol *Kirim WA* ke customer (`wa.me`) dengan pesan otomatis
  yang menyesuaikan konteks (konfirmasi booking, pengingat jadwal, servis selesai, pengingat servis rutin).
- **Laporan** — ringkasan pendapatan dan performa teknisi per rentang tanggal untuk Owner.

### Pengalaman Pengguna (UI/UX)
- **Responsif / mobile-friendly** — di HP tampil seperti aplikasi: **bottom navigation
  bar** yang mudah dijangkau jempol, dan **tabel yang berubah menjadi kartu** sehingga
  tidak perlu scroll ke samping. Di desktop tetap tampil sidebar + tabel penuh.
- **PWA (installable)** — dapat "di-install" ke home screen HP (Add to Home Screen),
  berjalan standalone layaknya aplikasi native, dengan ikon & splash screen SIMAC dan
  halaman fallback saat offline.
- **Pencarian di setiap daftar** — cari data di halaman Booking (ID, customer, no. HP,
  layanan, teknisi), Customer (nama/HP), dan User (nama/email/HP), dapat dikombinasikan
  dengan filter status.
- **Panduan pengguna per halaman** — panel bantuan yang bisa dibuka/tutup di tiap
  halaman; status buka/tutupnya diingat per halaman (localStorage).
- **Tombol berwarna jelas** — konsisten di seluruh aplikasi: biru (aksi utama), amber
  (edit), dan **merah (hapus / batalkan)**.
- **Identitas visual** — favicon & brand snowflake SIMAC, tema biru–slate.

---

## Tech Stack

| Komponen    | Teknologi                                            |
|-------------|------------------------------------------------------|
| Backend     | Laravel 13 (PHP 8.3+/8.4)                            |
| Frontend    | Blade + Bootstrap 5 & Bootstrap Icons (CDN)          |
| Database    | MySQL 8 (semua tabel prefix `app_`)                  |
| Environment | Docker Compose (nginx, php-fpm, mysql, phpMyAdmin)   |
| Auth        | Laravel built-in Auth (session)                      |

---

## Peran Pengguna

| Role              | Akses |
|-------------------|-------|
| **Admin**         | Kelola master data, customer, unit AC, booking harian, assign teknisi, catat pembayaran & ulasan, kelola user. |
| **Owner/Manager** | Dashboard & laporan, performa teknisi, pendapatan (mayoritas read-only). |
| **Teknisi**       | Daftar tugas, update status pekerjaan, riwayat tugas selesai. |

> Customer **tidak memiliki akun** — datanya dikelola oleh Admin (sesuai PRD).

---

## Menjalankan dengan Docker

```bash
# 1. Salin environment (opsional; .env sudah tersedia untuk pengembangan lokal)
cp .env.example .env

# 2. Build & jalankan seluruh stack
docker compose up -d --build

# 3. Generate app key (jika .env belum punya APP_KEY)
docker compose exec app php artisan key:generate

# 4. Migrasi + data demo
docker compose exec app php artisan migrate:fresh --seed
```

Aplikasi tersedia di:

| Layanan      | URL                       |
|--------------|---------------------------|
| Aplikasi     | http://localhost:8090     |
| phpMyAdmin   | http://localhost:8091     |
| MySQL (host) | `localhost:3308`          |

> Port di host bisa diubah lewat env `WEB_PORT`, `PMA_PORT`, `DB_FORWARD_PORT`
> bila terjadi bentrok.

---

## Akun Demo (setelah seed)

| Role      | Email                 | Password   |
|-----------|-----------------------|------------|
| Admin     | admin@simac.test      | `password` |
| Owner     | owner@simac.test      | `password` |
| Teknisi 1 | teknisi1@simac.test   | `password` |
| Teknisi 2 | teknisi2@simac.test   | `password` |
| Teknisi 3 | teknisi3@simac.test   | `password` |

---

## Alur Booking

```
pending → assigned → on_the_way → in_progress → completed
                                              ↘ cancelled (oleh admin)
```

Setiap perubahan status dicatat di `app_booking_histories` melalui
`App\Services\BookingService` di dalam **transaksi DB**. Menugaskan teknisi menandainya
`busy`; menyelesaikan atau membatalkan booking mengembalikannya `available`.

---

## Struktur Kode Penting

```
app/
  Enums/            UserRole, BookingStatus, TechnicianStatus, PaymentStatus, PaymentMethod
  Http/
    Controllers/    Auth, Dashboard, Booking, Customer, AcUnit, Service, Technician, Payment, Review, Report, User
    Middleware/     EnsureUserHasRole  (alias 'role')
  Models/           User, Customer, Technician, Service, AcUnit, Booking, BookingHistory, Payment, Review
  Providers/        AppServiceProvider (paginator Bootstrap 5)
  Services/         BookingService  (transisi status + histori, transaksional)
database/
  migrations/       skema tabel app_*
  seeders/          data demo (akun, layanan, customer, booking lintas status)
resources/views/
  layouts/          app (sidebar + bottom nav mobile), guest
  components/       page-guide (panduan per halaman), status-badge
  dashboard/        tampilan per role (admin, owner, technician)
  bookings, customers, ac_units, services, technicians, users, reports, auth
docker/nginx/       konfigurasi reverse proxy
```

---

## Pengembangan Lokal (tanpa Docker)

Butuh PHP 8.3+ dan Composer. Set `DB_CONNECTION=sqlite` di `.env`, lalu:

```bash
composer install
php artisan migrate:fresh --seed
php artisan serve
```

### Testing

```bash
php artisan test          # lokal (SQLite in-memory)
# atau di dalam container:
docker compose exec app php artisan test
```

Meliputi kontrol akses per role, alur login/logout, dan siklus hidup booking
(assign → progres status → completed, pembatasan teknisi, pembayaran & ulasan).

---

## Catatan Konvensi

- **Prefix tabel `app_`** diterapkan lewat `DB_PREFIX` pada `config/database.php`,
  sehingga seluruh tabel (termasuk bawaan Laravel) memakai prefix tersebut.
- Password ter-hash (bcrypt, default Laravel).
- Reset password via email tersedia (`MAIL_MAILER=log` saat development — tautan reset
  muncul di `storage/logs/laravel.log`).
- Pagination memakai tampilan **Bootstrap 5** (diatur di `AppServiceProvider`).

---

## Out of Scope (v1.0)

Payment gateway online, live GPS tracking, dan aplikasi mobile native — sesuai PRD.
(Antarmuka web sudah responsif untuk penggunaan di perangkat mobile.)
