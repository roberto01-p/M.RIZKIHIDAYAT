# KOINY — Digital Wallet & Marketplace (Project UTS PBO)

Aplikasi simulasi e-wallet dan marketplace berbasis PHP, MySQL/MariaDB, HTML, CSS, dan JavaScript.

## Menjalankan di Laragon
1. Ekstrak folder `KOINY-EWallet` ke `C:\laragon\www\`.
2. Jalankan Laragon dengan **Start All** (Apache dan MySQL).
3. Pastikan `config.php` sesuai konfigurasi lokal. Default: host `127.0.0.1`, user `root`, password kosong.
4. Buka `http://localhost/KOINY-EWallet/setup.php` untuk membuat database, tabel, akun demo, voucher, dan katalog produk awal.
5. Buka `http://localhost/KOINY-EWallet/`.

### Akun demo
- Pembeli utama: `arya@koiny.test` / `koiny123`
- Akun penerima transfer: `nadia@koiny.test` / `koiny123`
- Admin toko: `admin@koiny.test` / `admin123`

## Fitur yang tersedia
### Wallet
- Registrasi dengan nama, email, nomor HP, dan password.
- Login dengan email atau nomor HP.
- Dashboard saldo, top up simulasi, transfer antarakun, riwayat wallet.

### Marketplace
- Katalog produk dengan foto, deskripsi, varian, harga, stok, lokasi, dan video bila URL video ditambahkan admin.
- Pencarian dan filter kata kunci, kategori, rentang harga, lokasi, serta pengurutan rating/harga.
- Detail produk dan ulasan yang sudah masuk.
- Keranjang belanja, ubah jumlah, hitung subtotal.
- Wishlist.
- Simpan beberapa alamat pengiriman.
- Estimasi ongkir berdasarkan berat dan pilihan kurir simulasi.
- Voucher demo: `KOINY10`, `HEMAT15`, `ONGKIRKU`.
- Checkout dengan pilihan KOINY Balance, VA simulasi, QRIS simulasi, kartu simulasi, dan COD.
- Riwayat pesanan, nomor resi, dan tahapan tracking simulasi.
- Ulasan produk setelah pesanan berstatus selesai.
- Pusat bantuan / pesan pelanggan.

### Admin
- Login admin terpisah.
- Tambah dan edit katalog, harga, stok, berat, lokasi, foto, video URL, serta varian.
- Stok produk otomatis berkurang saat checkout berhasil.
- Rekap omzet pembayaran lunas, jumlah pesanan, dan daftar produk terlaris.

## Fitur yang masih berupa simulasi
- Top up tidak mengambil uang dari rekening atau kartu sungguhan.
- VA, QRIS, kartu, dan COD tidak terhubung ke payment gateway/penyedia pembayaran.
- Tarif kurir adalah estimasi lokal sederhana, bukan API ekspedisi.
- Nomor resi dan status tracking tidak terhubung ke kurir secara real-time.
- Live chat menyimpan pesan pelanggan; belum terhubung ke petugas manusia atau notifikasi real-time.
- Registrasi/login sosial, OTP, dan autentikasi nomor HP belum terintegrasi dengan provider pihak ketiga.
- Foto/video produk menggunakan URL; admin dapat memasukkan URL media yang dapat diakses browser.

Untuk layanan produksi diperlukan integrasi resmi payment gateway, API ekspedisi, OTP, penyimpanan media, HTTPS, proteksi CSRF, rate limiting, audit log, serta tinjauan keamanan dan kepatuhan regulator. Jangan gunakan data atau uang sungguhan.

## Penerapan PBO
- Kelas induk `User` memiliki atribut dasar dan method profil/autentikasi.
- Kelas turunan `WalletUser extends User` memiliki atribut dompet dan method top up, debit, credit, serta ringkasan saldo.
- Instansiasi: `new WalletUser($row)` di `api.php`.
- Enkapsulasi: saldo dan PIN memakai `private`; perubahan saldo melalui method kelas.
- Inheritance: `WalletUser` mewarisi `User` dan memanggil `parent::__construct()`.

## Struktur utama
- `index.php` — antarmuka wallet dan marketplace.
- `style.css` — desain biru dan pink muda.
- `app.js` — interaksi frontend dan Fetch API.
- `api.php` — endpoint backend.
- `config.php` — koneksi PDO.
- `classes.php` — kelas OOP.
- `setup.php` — pembuatan database, tabel, akun demo, voucher, dan produk awal.

**Penting:** Jalankan `setup.php` setelah meng-update versi proyek agar tabel marketplace dibuat. Hapus/batasi akses `setup.php` setelah instalasi pada lingkungan yang dipakai bersama.
