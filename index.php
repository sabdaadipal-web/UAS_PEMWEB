<?php
session_start();
// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login/login.php");
    exit;
}

// Menghubungkan ke database
require __DIR__ . '/config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Cafe POS Dashboard</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sidebar-bg: #342820;
            --sidebar-hover: #48382d;
            --sidebar-active-bg: #f9dfca;
            --sidebar-active-text: #342820;
            --sidebar-text: #bcaaa0;
            --bg-color: #f4f5f7;
            --primary-btn: #2e7d32;
            --primary-btn-hover: #1b5e20;
        }
        
        body { 
            background-color: var(--bg-color); 
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }
        
        /* Layout */
        .pos-layout {
            display: flex;
            height: 100vh;
        }
        
        /* Sidebar */
        .sidebar { 
            background-color: var(--sidebar-bg); 
            color: white; 
            width: 250px;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px;
            margin-bottom: 2rem;
        }
        
        .brand-icon {
            background: white;
            color: var(--sidebar-bg);
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .brand-text h4 {
            margin: 0;
            font-weight: 700;
            font-size: 18px;
        }
        
        .brand-text p {
            margin: 0;
            font-size: 11px;
            color: var(--sidebar-text);
        }
        
        .nav-link { 
            color: var(--sidebar-text); 
            border-radius: 12px;
            padding: 10px 15px;
            margin-bottom: 5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            color: white;
            background-color: var(--sidebar-hover);
        }
        
        .nav-link.active { 
            color: var(--sidebar-active-text); 
            background-color: var(--sidebar-active-bg); 
            font-weight: 600;
        }
        
        .logout-mt {
            margin-top: auto;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 2rem;
            overflow-y: auto;
        }
        
        .page-title {
            font-weight: 800;
            color: #1f1a17;
            margin-bottom: 1.5rem;
            font-size: 24px;
        }
        
        /* Categories */
        .category-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 1rem;
        }
        
        .category-scroll::-webkit-scrollbar { display: none; }
        
        .btn-category {
            border: 1px solid #ddd;
            background: white;
            color: #555;
            border-radius: 20px;
            padding: 6px 20px;
            font-weight: 500;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .btn-category.active {
            background-color: #342820;
            color: white;
            border-color: #342820;
        }
        
        /* Product Cards */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            padding-bottom: 2rem;
        }
        
        .menu-card { 
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: 0.2s;
            background: white;
            position: relative;
        }
        
        .menu-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .menu-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .menu-body {
            padding: 15px;
        }
        
        .menu-title {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 2px;
            color: #222;
        }
        
        .menu-subtitle {
            font-size: 12px;
            color: #888;
            margin-bottom: 12px;
            min-height: 18px;
        }
        
        .menu-price {
            font-weight: 700;
            font-size: 16px;
            color: #222;
        }
        
        .btn-add {
            background: #f4f5f7;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            transition: 0.2s;
        }
        
        .btn-add:hover {
            background: #e0e0e0;
        }
        
        .btn-add.selected {
            background: #342820;
            color: white;
        }
        
        .qty-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #342820;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Right Sidebar (Cart) */
        .cart-sidebar {
            width: 320px;
            background: white;
            border-left: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }
        
        .cart-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-title {
            font-weight: 700;
            font-size: 18px;
            margin: 0;
        }
        
        .cart-subtitle {
            font-size: 12px;
            color: #888;
            margin: 0;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 1.5rem;
        }
        
        .cart-item {
            display: flex;
            gap: 12px;
            margin-bottom: 1rem;
            background: white;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 12px;
        }
        
        .cart-item-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-title {
            font-weight: 600;
            font-size: 13px;
            margin: 0;
            color: #222;
        }
        
        .cart-item-subtitle {
            font-size: 10px;
            color: #888;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            font-weight: 600;
            font-size: 13px;
            text-align: right;
            margin: 0;
        }
        
        .qty-control {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f4f5f7;
            border-radius: 20px;
            padding: 2px 8px;
            width: fit-content;
        }
        
        .qty-btn {
            border: none;
            background: none;
            font-size: 14px;
            color: #555;
            padding: 0 4px;
        }
        
        .qty-val {
            font-size: 13px;
            font-weight: 600;
        }
        
        .cart-footer {
            padding: 1.5rem;
            border-top: 1px solid #f0f0f0;
            background: white;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 18px;
            font-weight: 800;
            color: #222;
        }
        
        .btn-process {
            background-color: var(--primary-btn);
            color: white;
            border: none;
            border-radius: 10px;
            width: 100%;
            padding: 12px;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-process:hover {
            background-color: var(--primary-btn-hover);
            color: white;
        }
        
        /* Modals */
        .modal-content {
            border-radius: 16px;
            border: none;
        }
        
        .payment-method-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }
        
        .payment-method:hover {
            background: #f8f9fa;
        }
        
        .payment-method.active {
            border-color: var(--sidebar-bg);
            background-color: #fcfaf8;
            font-weight: 600;
            box-shadow: 0 0 0 1px var(--sidebar-bg);
            position: relative;
        }
        .payment-method.active::after {
            content: '';
            position: absolute;
            top: -4px;
            right: -4px;
            width: 12px;
            height: 12px;
            background: var(--sidebar-bg);
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .payment-icon {
            font-size: 24px;
            margin-bottom: 5px;
            color: var(--sidebar-bg);
        }
        
        .amount-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .amount-btn-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-amount {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }
        
        .btn-amount:hover {
            background: #f0f0f0;
        }
        
        .change-box {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .change-label {
            color: var(--primary-btn);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .change-value {
            color: var(--primary-btn);
            font-weight: 800;
            font-size: 18px;
        }
        
        /* Receipt Print Layout */
        @media print {
            body * { visibility: hidden; }
            #receiptArea, #receiptArea * { visibility: visible; }
            #receiptArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="pos-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand-logo">
            <div class="brand-icon">☕</div>
            <div class="brand-text">
                <h4>E-Cafe</h4>
                <p>Premium POS System</p>
            </div>
        </div>
        
        <nav class="nav flex-column gap-1">
            <a href="index.php" class="nav-link active">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="menu.php" class="nav-link">
                <i class="bi bi-cup-hot"></i> Menu Cafe
            </a>
            <a href="orders.php" class="nav-link">
                <i class="bi bi-receipt"></i> Orders
            </a>
        </nav>
        
        <div class="logout-mt">
            <a href="login/logout.php" class="nav-link">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">E-Cafe</h2>
        
        <div class="product-grid" id="productGrid">
            <!-- Items akan diisi via JS -->
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-header">
            <div>
                <h3 class="cart-title">Current Order</h3>
                <p class="cart-subtitle">Order #1042 &bull; Dine In</p>
            </div>
            <button class="btn btn-sm btn-outline-danger border-0" onclick="clearCart()">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
        
        <div class="cart-items" id="cartItems">
            <!-- Cart items via JS -->
            <div class="text-center text-muted mt-5" id="emptyCartMsg">
                <i class="bi bi-cart-x fs-1 mb-2"></i>
                <p>Keranjang masih kosong</p>
            </div>
        </div>
        
        <div class="cart-footer">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotal">Rp 0</span>
            </div>
            <div class="summary-row">
                <span>Tax (10%)</span>
                <span id="tax">Rp 0</span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span id="total">Rp 0</span>
            </div>
            <button class="btn-process" onclick="showPaymentModal()" id="btnProcess" disabled>
                <i class="bi bi-cash-stack"></i> Process Payment
            </button>
        </div>
    </div>
</div>

<!-- Modal Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Process Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small fw-bold mb-2">METODE PEMBAYARAN</p>
        <div class="payment-method-grid">
            <div class="payment-method active">
                <div class="payment-icon"><i class="bi bi-cash"></i></div>
                <div class="small fw-semibold">Cash</div>
            </div>
            <div class="payment-method">
                <div class="payment-icon"><i class="bi bi-credit-card"></i></div>
                <div class="small fw-semibold">Debit Card</div>
            </div>
            <div class="payment-method">
                <div class="payment-icon"><i class="bi bi-qr-code-scan"></i></div>
                <div class="small fw-semibold">Qris</div>
            </div>
        </div>
        
        <div class="amount-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted fw-semibold small">Total Pembayaran</span>
                <span class="fs-4 fw-bold" id="payTotal">Rp 0</span>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold small">Uang Tunai Diterima</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">Rp</span>
                    <input type="number" class="form-control form-control-lg fw-bold" id="cashReceived" placeholder="0">
                </div>
            </div>
            
            <div class="amount-btn-grid" id="quickAmounts">
                <!-- Diisi via JS -->
            </div>
            
            <div class="change-box">
                <div class="change-label">
                    <i class="bi bi-cash-stack"></i> Kembalian
                </div>
                <div class="change-value" id="changeAmount">Rp 0</div>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Batal</button>
        <button type="button" class="btn btn-success" style="background-color: var(--primary-btn); border-radius: 8px; font-weight: 600;" onclick="showReceipt()">
            Bayar Sekarang <i class="bi bi-check-circle ms-1"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 bg-light">
        <h5 class="modal-title fw-bold">Print Preview</h5>
        <div>
            <button class="btn btn-sm btn-light border me-1" onclick="window.print()"><i class="bi bi-printer"></i></button>
            <button class="btn btn-sm btn-light border" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
      <div class="modal-body bg-light d-flex justify-content-center p-4">
        
        <!-- Receipt Content -->
        <div id="receiptArea" style="width: 320px; background: white; padding: 30px 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); font-family: monospace;">
            <div class="text-center mb-4">
                <div style="font-size: 30px; margin-bottom: 10px;"><i class="bi bi-cup-hot"></i></div>
                <h4 class="fw-bold mb-1" style="font-family: 'Inter', sans-serif;">E-CAFE</h4>
                <p class="small mb-0 text-muted">Jl. Ciledug Raya No. 123, Jakarta Selatan</p>
                <p class="small mb-0 text-muted">Telp: 081234567890</p>
            </div>
            
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Order No:</span>
                <span class="fw-bold" id="receiptOrderNo">#ORD-1042</span>
            </div>
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Date:</span>
                <span class="fw-bold"><?= date('d M Y, H:i') ?></span>
            </div>
            <div class="d-flex justify-content-between small mb-4">
                <span class="text-muted">Cashier:</span>
                <span class="fw-bold">Admin</span>
            </div>
            
            <div class="d-flex justify-content-between small border-bottom pb-2 mb-2 text-muted fw-bold">
                <span>Qty Item</span>
                <span>Total</span>
            </div>
            
            <div id="receiptItems">
                <!-- JS populated -->
            </div>
            
            <div class="border-top pt-2 mt-3">
                <div class="d-flex justify-content-between small mb-1 text-muted">
                    <span>Subtotal</span>
                    <span id="receiptSubtotal">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between small mb-2 text-muted">
                    <span>Tax (10%)</span>
                    <span id="receiptTax">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-4 mt-2 pt-2 border-top">
                    <span class="fw-bold">TOTAL</span>
                    <span class="fw-bold fs-5" id="receiptTotal">Rp 0</span>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="small fw-bold mb-1">Terima Kasih Atas Kunjungan Anda!</p>
                <p class="text-muted" style="font-size: 10px;"><i class="bi bi-cup"></i> POWERED BY E-CAFE POS</p>
            </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    <?php
    $result = mysqli_query($koneksi, "SELECT * FROM produk");
    $db_products = [];
    while($row = mysqli_fetch_assoc($result)) {
        $db_products[] = [
            'id' => (int)$row['id_produk'],
            'name' => $row['nama_produk'],
            'stok' => (int)$row['stok'],
            'price' => (int)$row['harga'],
            'img' => $row['gambar'] ?: 'https://via.placeholder.com/400'
        ];
    }
    ?>
    // Data produk dari database
    const products = <?= json_encode($db_products) ?>;

    let cart = [];
    const taxRate = 0.1;

    // Format Rupiah
    const formatRp = (angka) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka).replace('Rp', 'Rp ');
    }

    // Render Produk
    const renderProducts = () => {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';
        
        products.forEach(p => {
            const qty = cart.find(item => item.id === p.id)?.qty || 0;
            const isHabis = p.stok <= 0;
            
            const badgeHtml = qty > 0 ? `<div class="qty-badge">${qty}</div>` : '';
            const btnClass = isHabis ? 'btn-add text-secondary border-secondary' : (qty > 0 ? 'btn-add selected' : 'btn-add');
            const btnIcon = isHabis ? '<i class="bi bi-x-lg"></i>' : (qty > 0 ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-plus-lg"></i>');
            const clickHandler = isHabis ? '' : `onclick="addToCart(${p.id})"`;
            const btnClickHandler = isHabis ? '' : `onclick="event.stopPropagation(); addToCart(${p.id})"`;
            const opacityClass = isHabis ? 'opacity-50' : '';
            
            let stokText = `<p class="menu-subtitle text-muted mb-1" style="font-size: 11px;">Sisa Stok: ${p.stok}</p>`;
            if (isHabis) {
                stokText = `<p class="menu-subtitle text-danger fw-bold mb-1" style="font-size: 11px;">HABIS</p>`;
            }
            
            grid.innerHTML += `
                <div class="card menu-card ${opacityClass}" ${clickHandler} style="${isHabis ? 'cursor:not-allowed;' : ''}">
                    ${badgeHtml}
                    <img src="${p.img}" class="menu-img" alt="${p.name}" style="${isHabis ? 'filter: grayscale(100%);' : ''}">
                    <div class="menu-body">
                        <h6 class="menu-title">${p.name}</h6>
                        ${stokText}
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="menu-price">${formatRp(p.price)}</span>
                            <button class="${btnClass}" ${btnClickHandler} ${isHabis ? 'disabled style="background:#eee;"' : ''}>
                                ${btnIcon}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    // Cart Logic
    const addToCart = (id) => {
        const product = products.find(p => p.id === id);
        
        if(product.stok <= 0) {
            alert('Maaf, stok item ini sudah habis.');
            return;
        }
        
        const existing = cart.find(item => item.id === id);
        const currentQty = existing ? existing.qty : 0;
        
        if (currentQty >= product.stok) {
            alert('Maksimal pemesanan adalah ' + product.stok + ' (Sesuai sisa stok).');
            return;
        }
        
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ ...product, qty: 1 });
        }
        updateCart();
    }

    const updateQty = (id, delta) => {
        const index = cart.findIndex(item => item.id === id);
        if (index > -1) {
            const product = products.find(p => p.id === id);
            const newQty = cart[index].qty + delta;
            
            if (newQty > product.stok) {
                alert('Maksimal pemesanan adalah ' + product.stok + ' (Sesuai sisa stok).');
                return;
            }
            
            cart[index].qty = newQty;
            if (cart[index].qty <= 0) {
                cart.splice(index, 1);
            }
            updateCart();
        }
    }

    const clearCart = () => {
        if(confirm('Apakah Anda yakin ingin mengosongkan pesanan?')) {
            cart = [];
            updateCart();
        }
    }

    const updateCart = () => {
        renderProducts(); // Update badges
        
        const cartItemsDiv = document.getElementById('cartItems');
        const emptyMsg = document.getElementById('emptyCartMsg');
        const btnProcess = document.getElementById('btnProcess');
        
        let subtotal = 0;
        
        if (cart.length === 0) {
            cartItemsDiv.innerHTML = `<div class="text-center text-muted mt-5" id="emptyCartMsg">
                <i class="bi bi-cart-x fs-1 mb-2"></i>
                <p>Keranjang masih kosong</p>
            </div>`;
            btnProcess.disabled = true;
        } else {
            cartItemsDiv.innerHTML = '';
            btnProcess.disabled = false;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.qty;
                subtotal += itemTotal;
                
                cartItemsDiv.innerHTML += `
                    <div class="cart-item">
                        <img src="${item.img}" class="cart-item-img" alt="${item.name}">
                        <div class="cart-item-details">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="cart-item-title">${item.name}</h6>
                                    <p class="cart-item-subtitle text-muted mb-0" style="font-size: 11px;">Sisa Stok: ${item.stok}</p>
                                </div>
                                <div class="cart-item-price">${formatRp(itemTotal)}</div>
                            </div>
                            <div class="qty-control">
                                <button class="qty-btn" onclick="updateQty(${item.id}, -1)"><i class="bi bi-dash"></i></button>
                                <span class="qty-val">${item.qty}</span>
                                <button class="qty-btn" onclick="updateQty(${item.id}, 1)"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        const tax = subtotal * taxRate;
        const total = subtotal + tax;
        
        document.getElementById('subtotal').innerText = formatRp(subtotal);
        document.getElementById('tax').innerText = formatRp(tax);
        document.getElementById('total').innerText = formatRp(total);
        
        // Simpan total ke dataset untuk modal pembayaran
        btnProcess.dataset.total = total;
    }

    // Modal Pembayaran Logic
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    const cashInput = document.getElementById('cashReceived');
    
    const showPaymentModal = () => {
        const total = parseFloat(document.getElementById('btnProcess').dataset.total);
        document.getElementById('payTotal').innerText = formatRp(total);
        cashInput.value = '';
        document.getElementById('changeAmount').innerText = 'Rp 0';
        
        // Generate Quick Amounts
        const qa = document.getElementById('quickAmounts');
        qa.innerHTML = '';
        
        // Logika sederhana uang pas dan pecahan
        const amounts = [
            Math.ceil(total / 50000) * 50000, 
            Math.ceil(total / 100000) * 100000
        ];
        
        let uniqueAmounts = [...new Set(amounts)].filter(v => v > total);
        if(uniqueAmounts.length < 2) uniqueAmounts.push(total + 50000); // fallback
        
        qa.innerHTML += `<button class="btn-amount" onclick="setCash(${uniqueAmounts[0]})">${formatRp(uniqueAmounts[0])}</button>`;
        qa.innerHTML += `<button class="btn-amount" onclick="setCash(${uniqueAmounts[1]})">${formatRp(uniqueAmounts[1])}</button>`;
        qa.innerHTML += `<button class="btn-amount" onclick="setCash(${total})">Uang Pas</button>`;
        
        paymentModal.show();
    }
    
    const setCash = (amount) => {
        cashInput.value = amount;
        calculateChange();
    }
    
    cashInput.addEventListener('input', () => {
        calculateChange();
    });
    
    const calculateChange = () => {
        const total = parseFloat(document.getElementById('btnProcess').dataset.total);
        const cash = parseFloat(cashInput.value) || 0;
        const change = cash - total;
        
        if (change >= 0) {
            document.getElementById('changeAmount').innerText = formatRp(change);
            document.getElementById('changeAmount').parentElement.style.backgroundColor = '#e8f5e9';
            document.getElementById('changeAmount').parentElement.style.borderColor = '#c8e6c9';
            document.getElementById('changeAmount').style.color = 'var(--primary-btn)';
        } else {
            document.getElementById('changeAmount').innerText = 'Kurang ' + formatRp(Math.abs(change));
            document.getElementById('changeAmount').parentElement.style.backgroundColor = '#ffebee';
            document.getElementById('changeAmount').parentElement.style.borderColor = '#ffcdd2';
            document.getElementById('changeAmount').style.color = '#c62828';
        }
    }
    
    // Receipt Logic
    const showReceipt = async () => {
        const total = parseFloat(document.getElementById('btnProcess').dataset.total);
        const cash = parseFloat(cashInput.value) || 0;
        
        if (cash < total) {
            alert('Uang tunai kurang!');
            return;
        }
        
        // Simpan ke database via AJAX
        try {
            const response = await fetch('api_checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cart: cart,
                    total: total,
                    cash: cash,
                    type: 'Dine In'
                })
            });
            const data = await response.json();
            
            if(data.success) {
                paymentModal.hide();
                
                // Update nomor order di struk
                document.getElementById('receiptOrderNo').innerText = '#ORD-' + data.order_id;
                
                // Populate receipt
                const receiptItems = document.getElementById('receiptItems');
                receiptItems.innerHTML = '';
                
                let subtotal = 0;
                cart.forEach(item => {
                    const itemTotal = item.price * item.qty;
                    subtotal += itemTotal;
                    receiptItems.innerHTML += `
                        <div class="d-flex justify-content-between small mb-2">
                            <div style="width: 70%">
                                <div class="d-flex gap-2">
                                    <span class="fw-bold">${item.qty}</span>
                                    <div>
                                        <div class="fw-semibold text-dark">${item.name}</div>
                                        <div class="text-muted" style="font-size: 10px;">@ ${formatRp(item.price)}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="fw-semibold">${formatRp(itemTotal)}</div>
                        </div>
                    `;
                });
                
                const tax = subtotal * taxRate;
                document.getElementById('receiptSubtotal').innerText = formatRp(subtotal);
                document.getElementById('receiptTax').innerText = formatRp(tax);
                document.getElementById('receiptTotal').innerText = formatRp(total);
                
                receiptModal.show();
                
                // Reset cart after print preview shown (simulasi selesai transaksi)
                cart = [];
                updateCart();
            } else {
                alert('Gagal memproses pesanan: ' + data.error);
            }
        } catch (error) {
            alert('Terjadi kesalahan jaringan');
        }
    }

    // Init
    renderProducts();
</script>
</body>
</html>