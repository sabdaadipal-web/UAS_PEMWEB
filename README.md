# E-Cafe Point of Sales (POS) System
username : admin
password : password

## Deskripsi Program
E-Cafe POS adalah sistem kasir berbasis web yang dirancang khusus untuk mempermudah operasional dan manajemen sebuah kafe. Program ini dikembangkan menggunakan **Full PHP Native** sebagai *backend*, **HTML/CSS/JS (Vanilla)**, dan **Bootstrap 5 (via CDN)** untuk tampilan antarmuka (UI) yang responsif, modern, serta interaktif.

## Fitur Utama
1. **Sistem Kasir Interaktif (Dashboard)**: Penambahan pesanan ke keranjang belanja secara *real-time* via JavaScript dengan fitur validasi keamanan (mencegah pesanan melebihi sisa stok).
2. **Manajemen Menu**: Fitur CRUD terintegrasi (Tambah Menu dengan *upload* foto, Edit, Hapus) untuk mengatur daftar produk dan ketersediaan stok secara terpusat.
3. **Riwayat Pesanan (Order History)**: Melacak transaksi harian, mengkalkulasi statistik pendapatan otomatis, dan mengelola status pesanan (Pending / Selesai).
4. **Cetak Struk Otomatis (Print Preview)**: Fitur cetak bukti transaksi digital yang sangat elegan dan tertata, dapat dicetak langsung saat pembayaran selesai maupun dicetak ulang melalui riwayat pesanan.

## Arsitektur dan Teknologi
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla ES6 dengan API `fetch`), Bootstrap 5 CDN, Bootstrap Icons.
- **Backend**: PHP 8+ Native (Prosedural dengan MySQLi).
- **Database**: MySQL (RDBMS).

---

## Relasi dan Integrasi Database (ERD Logis)
Kekuatan utama dari program ini berada pada struktur *Database Relasional*-nya. Terdapat **3 tabel transaksi utama** yang saling terhubung untuk memastikan konsistensi dan integritas data (ACID).

### 1. Tabel `produk` (Master Data)
Menyimpan semua katalog menu kafe.
- `id_produk` (Primary Key)
- `nama_produk`
- `harga`
- `stok`
- `gambar`

### 2. Tabel `pesanan` (Transaction Header)
Menyimpan informasi utama (kepala) dari sebuah transaksi secara umum ketika pembayaran diselesaikan.
- `id_pesanan` (Primary Key)
- `tanggal`
- `total_harga`
- `nama_pelanggan`
- `tipe_pesanan`
- `status_pesanan`

### 3. Tabel `detail_pesanan` (Transaction Detail / Pivot)
Tabel ini sangat krusial karena bertindak sebagai relasi (jembatan *Many-to-Many*) antara tabel `pesanan` dan `produk`. Ini mencatat rincian spesifik menu apa saja yang ada di dalam satu bukti transaksi.
- `id_detail` (Primary Key)
- `id_pesanan` (Foreign Key relasi ke `pesanan.id_pesanan`)
- `id_produk` (Foreign Key relasi ke `produk.id_produk`)
- `qty` (Jumlah pesanan)
- `subtotal` (Harga x Jumlah)

*(Program ini juga memiliki 1 tabel tambahan yaitu tabel `users` yang berdiri secara independen khusus untuk mengotentikasi login admin/kasir).*

---

## Bagaimana Ketiga Tabel Ini Terintegrasi? (Alur Checkout)
Kehebatan integrasi sistem ini dapat terlihat paling jelas saat Kasir menekan tombol **"Bayar Sekarang"**. Pada detik tersebut, program mengeksekusi tiga operasi database beruntun secara mulus:

1. **Tahap 1 (Mencatat Bukti Transaksi):** 
   Program mengeksekusi perintah `INSERT INTO pesanan`. Identitas pesanan (Total Bayar, Nama Pelanggan) direkam. Sistem kemudian secara instan menangkap **Nomor ID Pesanan (Order ID)** yang baru saja terbuat (menggunakan `mysqli_insert_id`).
   
2. **Tahap 2 (Merekam Item & Membentuk Relasi):** 
   Sistem membaca seluruh daftar pesanan yang ada di dalam keranjang kasir (secara *looping*). Untuk setiap item, sistem memasukkannya ke tabel `detail_pesanan` dengan menempelkan *Order ID* dari Tahap 1 dan *Product ID* dari barang tersebut. Pada tahap inilah **tiga tabel tersebut resmi saling terpaut erat**.
   
3. **Tahap 3 (Sinkronisasi Otomatis Stok):** 
   Masih di dalam putaran yang sama, sistem secara otomatis melakukan perintah `UPDATE produk SET stok = stok - qty`. Stok setiap menu akan langsung terpotong secara akurat berdasarkan kuantitas yang baru saja dijual.

Berkat relasi kokoh ketiga tabel ini, halaman Riwayat Pesanan (`orders.php`) mampu menarik kembali data masa lalu secara sempurna—menggabungkan data siapa pelanggannya, apa saja yang ia pesan, beserta harga asli produknya—untuk dirender menjadi sebuah struk transaksi yang utuh kapan saja!
