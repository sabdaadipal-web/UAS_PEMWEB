<?php
session_start();
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require __DIR__ . '/config/koneksi.php';

// Ambil payload JSON dari JS fetch
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

$cart = $data['cart'];
$total = $data['total']; // Total setelah tax
$cash = $data['cash'];
$type = $data['type'] ?? 'Dine In'; 
// Di DB kita belum buat kolom tipe pesanan (Dine In/Take away) dan status. 
// Tapi sementara simpan total_harga saja sesuai skema pesanan saat ini, 
// atau kita tambahkan kolom tipe_pesanan, nama_pelanggan, status ke tabel pesanan?
// Karena tabel pesanan hanya punya: id_pesanan, tanggal, total_harga.
// Biar sesuai dengan UI, saya akan ALTER TABLE pesanan dulu jika kolom belum ada!

// Kolom nama_pelanggan, tipe_pesanan, dan status_pesanan sudah ditambahkan ke database.
$nama_pelanggan = "Pelanggan Umum"; // Bisa ditangkap dari form nanti

// 1. Insert ke tabel pesanan
$stmt = mysqli_prepare($koneksi, "INSERT INTO pesanan (total_harga, nama_pelanggan, tipe_pesanan, status_pesanan) VALUES (?, ?, ?, 'Pending')");
mysqli_stmt_bind_param($stmt, "iss", $total, $nama_pelanggan, $type);

if (mysqli_stmt_execute($stmt)) {
    $id_pesanan = mysqli_insert_id($koneksi);
    
    // 2. Insert ke detail_pesanan
    $stmt_detail = mysqli_prepare($koneksi, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES (?, ?, ?, ?)");
    
    // 3. Update stok produk
    $stmt_stok = mysqli_prepare($koneksi, "UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
    
    foreach ($cart as $item) {
        $id_produk = $item['id'];
        $qty = $item['qty'];
        $subtotal = $item['price'] * $qty;
        
        // Insert detail
        mysqli_stmt_bind_param($stmt_detail, "iiii", $id_pesanan, $id_produk, $qty, $subtotal);
        mysqli_stmt_execute($stmt_detail);
        
        // Kurangi stok
        mysqli_stmt_bind_param($stmt_stok, "ii", $qty, $id_produk);
        mysqli_stmt_execute($stmt_stok);
    }
    
    echo json_encode(['success' => true, 'order_id' => $id_pesanan]);
} else {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($koneksi)]);
}
?>
