<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login/login.php");
    exit;
}
require __DIR__ . '/config/koneksi.php';

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $q_status = mysqli_query($koneksi, "SELECT status_pesanan FROM pesanan WHERE id_pesanan = $id");
    if($r = mysqli_fetch_assoc($q_status)) {
        $new_status = (strtolower($r['status_pesanan']) == 'selesai') ? 'Pending' : 'Selesai';
        mysqli_query($koneksi, "UPDATE pesanan SET status_pesanan = '$new_status' WHERE id_pesanan = $id");
    }
    header("Location: orders.php");
    exit;
}

$q = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY id_pesanan DESC");
$orders = [];
$total_pendapatan = 0;
$selesai = 0;
$pending = 0;
while($row = mysqli_fetch_assoc($q)) {
    $orders[] = $row;
    $total_pendapatan += $row['total_harga'];
    if(strtolower($row['status_pesanan']) == 'selesai') $selesai++;
    else $pending++;
}
$total_order = count($orders);

// Fetch all details to inject into JS for modals
$q_details = mysqli_query($koneksi, "
    SELECT dp.*, p.nama_produk, p.harga 
    FROM detail_pesanan dp
    JOIN produk p ON dp.id_produk = p.id_produk
");
$all_details = [];
while($d = mysqli_fetch_assoc($q_details)) {
    $all_details[$d['id_pesanan']][] = $d;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - E-Cafe POS</title>
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
            --bg-color: #f8f9fb;
            --text-dark: #1f1a17;
            --border-color: #eaeaea;
        }
        
        body { 
            background-color: var(--bg-color); 
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        
        /* Layout */
        .pos-layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar { 
            background-color: var(--sidebar-bg); 
            color: white; 
            width: 250px;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
            position: fixed;
            height: 100vh;
            z-index: 100;
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
            margin-left: 250px;
            padding: 2rem 2.5rem;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
            font-size: 24px;
        }
        
        .btn-group-period .btn {
            background: white;
            border: 1px solid var(--border-color);
            color: #555;
            font-size: 13px;
            font-weight: 500;
        }
        
        .btn-group-period .btn.active {
            background: #f8f9fb;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .summary-card::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #fdfdfd;
            z-index: 0;
        }
        
        .summary-content {
            position: relative;
            z-index: 1;
        }
        
        .summary-label {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .summary-value {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 28px;
            margin: 0;
        }
        
        /* Table Controls */
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        
        .table-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table-header h5 {
            margin: 0;
            font-weight: 700;
        }
        
        /* Table Styles */
        .table {
            margin: 0;
        }
        
        .table th {
            font-size: 11px;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            padding: 15px;
            background: #fdfdfd;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            color: #444;
            font-size: 13px;
        }
        
        .badge-type {
            background: #f0f0f0;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-selesai { background: #e8f5e9; color: #2e7d32; }
        .status-pending { background: #fff3e0; color: #ef6c00; }
        
        .action-btn {
            border: none;
            background: none;
            color: #888;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s;
        }
        
        .action-btn:hover { color: var(--text-dark); }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            font-size: 13px;
            color: #666;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
            margin: 0;
        }
        
        .page-item {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            cursor: pointer;
            color: #555;
            text-decoration: none;
        }
        
        .page-item:hover { background: #f0f0f0; }
        .page-item.active { background: #342820; color: white; }
        
        /* Modal Detail */
        .modal-content {
            border-radius: 12px;
            border: none;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }
        
        .modal-title { font-weight: 800; }
        
        .modal-body { padding: 1.5rem; }
        
        .info-box {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info-col p {
            margin: 0;
            font-size: 12px;
            color: #888;
            margin-bottom: 4px;
        }
        
        .info-col h6 {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
            color: #222;
        }
        
        .items-table {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: #fcfcfc;
            font-size: 12px;
            color: #888;
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .items-table td {
            padding: 12px 15px;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
            color: #444;
        }
        
        .summary-totals {
            width: 200px;
            margin-left: auto;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 800;
            color: #222;
            margin-top: 15px;
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-cancel {
            background: #ffebee;
            color: #c62828;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .btn-edit {
            background: #fff8e1;
            color: #f57f17;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .btn-done {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
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
                <p>Premium POS</p>
            </div>
        </div>
        
        <nav class="nav flex-column gap-1">
            <a href="index.php" class="nav-link">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="menu.php" class="nav-link">
                <i class="bi bi-cup-hot"></i> Menu Cafe
            </a>
            <a href="orders.php" class="nav-link active">
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
        <div class="header-actions">
            <h2 class="page-title">Daftar Pesanan</h2>
        </div>
            
            <!-- Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-content">
                        <div class="summary-label">
                            <i class="bi bi-bag"></i> Total Order Hari Ini
                        </div>
                        <h3 class="summary-value"><?= $total_order ?></h3>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-content">
                        <div class="summary-label">
                            <i class="bi bi-wallet2"></i> Total Pendapatan Hari Ini
                        </div>
                        <h3 class="summary-value" style="font-size: 24px;">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-content">
                        <div class="summary-label" style="color:#2e7d32;">
                            <i class="bi bi-check-circle"></i> Pesanan Selesai
                        </div>
                        <h3 class="summary-value"><?= $selesai ?></h3>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-content">
                        <div class="summary-label" style="color:#ef6c00;">
                            <i class="bi bi-clock"></i> Pesanan Pending
                        </div>
                        <h3 class="summary-value"><?= $pending ?></h3>
                    </div>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="table-container">
                <div class="table-header">
                    <h5>Recent Orders</h5>
                </div>
                
                <table class="table table-borderless table-hover">
                    <thead>
                        <tr>
                            <th width="12%">ID ORDER</th>
                            <th width="15%">TANGGAL & JAM</th>
                            <th width="15%">NAMA PELANGGAN</th>
                            <th width="15%">NAMA KASIR</th>
                            <th width="13%">TYPE</th>
                            <th width="15%">TOTAL PAYMENT</th>
                            <th width="10%">STATUS PESANAN</th>
                            <th width="5%" class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $o): 
                            $date = date('d M Y, H:i', strtotime($o['tanggal']));
                            $status_class = strtolower($o['status_pesanan']) == 'selesai' ? 'status-selesai' : 'status-pending';
                            $icon_type = strtolower($o['tipe_pesanan']) == 'dine in' ? 'bi-shop' : 'bi-bag';
                        ?>
                        <tr>
                            <td class="fw-bold">#ORD-<?= str_pad($o['id_pesanan'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= str_replace(', ', ',<br>', $date) ?></td>
                            <td><?= htmlspecialchars($o['nama_pelanggan']) ?></td>
                            <td>Admin</td>
                            <td><span class="badge-type"><i class="bi <?= $icon_type ?>"></i> <?= htmlspecialchars($o['tipe_pesanan']) ?></span></td>
                            <td class="fw-bold">Rp <?= number_format($o['total_harga'], 0, ',', '.') ?></td>
                            <td><span class="badge-status <?= $status_class ?>">&bull; <?= htmlspecialchars($o['status_pesanan']) ?></span></td>
                            <td class="text-center">
                                <button class="action-btn" onclick="openDetail(<?= $o['id_pesanan'] ?>)"><i class="bi bi-eye"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($orders)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Belum ada transaksi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="pagination-container">
                    <div>Showing <?= $total_order ?> entries</div>
                    <div class="pagination">
                        <a href="#" class="page-item" style="width: auto; padding: 0 10px;">Prev</a>
                        <a href="#" class="page-item active">1</a>
                        <a href="#" class="page-item" style="width: auto; padding: 0 10px;">Next</a>
                    </div>
                </div>
            </div>
            
    </div>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal fade" id="modalDetail" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 bg-light">
        <h5 class="modal-title fw-bold">Detail Struk</h5>
        <div>
            <button class="btn btn-sm btn-light border me-1" onclick="printReceipt()"><i class="bi bi-printer"></i></button>
            <button class="btn btn-sm btn-light border" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
      <div class="modal-body bg-light d-flex justify-content-center p-4">
        
        <div id="receiptArea" style="width: 320px; background: white; padding: 30px 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); font-family: monospace;">
            <div class="text-center mb-4">
                <div style="font-size: 30px; margin-bottom: 10px;"><i class="bi bi-cup-hot"></i></div>
                <h4 class="fw-bold mb-1" style="font-family: 'Inter', sans-serif;">E-CAFE</h4>
                <p class="small mb-0 text-muted">Jl. Ciledug Raya No. 123</p>
                <p class="small mb-0 text-muted">Pelanggan: <span id="mdlCustomer"></span></p>
            </div>
            
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Order No:</span>
                <span class="fw-bold" id="receiptOrderNo">-</span>
            </div>
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Date:</span>
                <span class="fw-bold" id="mdlDate">-</span>
            </div>
            <div class="d-flex justify-content-between small mb-4">
                <span class="text-muted">Type:</span>
                <span class="fw-bold" id="mdlType">-</span>
            </div>
            
            <div class="d-flex justify-content-between small border-bottom pb-2 mb-2 text-muted fw-bold">
                <span>Qty Item</span>
                <span>Total</span>
            </div>
            
            <div id="mdlItems">
            </div>
            
            <div class="border-top pt-2 mt-3">
                <div class="d-flex justify-content-between small mb-1 text-muted">
                    <span>Subtotal</span>
                    <span id="mdlSubtotal">-</span>
                </div>
                <div class="d-flex justify-content-between small mb-2 text-muted">
                    <span>Tax (10%)</span>
                    <span id="mdlTax">-</span>
                </div>
                <div class="d-flex justify-content-between mb-4 mt-2 pt-2 border-top">
                    <span class="fw-bold">TOTAL</span>
                    <span class="fw-bold fs-5" id="mdlTotal">-</span>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between bg-light border-0">
          <a href="#" id="btnToggleStatus" class="btn btn-warning w-100 fw-bold">Ubah Status Pesanan</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const orders = <?= json_encode($orders) ?>;
    const allDetails = <?= json_encode($all_details) ?>;
    const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
    
    function formatRp(num) {
        return 'Rp ' + parseInt(num).toLocaleString('id-ID');
    }

    function openDetail(id_pesanan) {
        const order = orders.find(o => parseInt(o.id_pesanan) === parseInt(id_pesanan));
        const details = allDetails[id_pesanan] || [];
        
        if(order) {
            document.getElementById('receiptOrderNo').innerText = '#ORD-' + String(order.id_pesanan).padStart(4, '0');
            document.getElementById('mdlCustomer').innerText = order.nama_pelanggan;
            document.getElementById('mdlDate').innerText = order.tanggal;
            document.getElementById('mdlType').innerText = order.tipe_pesanan;
            
            let html = '';
            let subtotal = 0;
            
            details.forEach(d => {
                subtotal += parseInt(d.subtotal);
                html += `
                <div class="d-flex justify-content-between small mb-2">
                    <div style="width: 70%">
                        <div class="d-flex gap-2">
                            <span class="fw-bold">${d.qty}</span>
                            <div>
                                <div class="fw-semibold text-dark">${d.nama_produk}</div>
                                <div class="text-muted" style="font-size: 10px;">@ ${formatRp(d.harga)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="fw-semibold">${formatRp(d.subtotal)}</div>
                </div>`;
            });
            
            document.getElementById('mdlItems').innerHTML = html;
            
            const tax = subtotal * 0.1;
            document.getElementById('mdlSubtotal').innerText = formatRp(subtotal);
            document.getElementById('mdlTax').innerText = formatRp(tax);
            document.getElementById('mdlTotal').innerText = formatRp(order.total_harga);
            
            const btnStatus = document.getElementById('btnToggleStatus');
            btnStatus.href = `orders.php?toggle=${order.id_pesanan}`;
            
            if (order.status_pesanan.toLowerCase() === 'selesai') {
                btnStatus.className = 'btn btn-outline-warning w-100 fw-bold';
                btnStatus.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Tandai Pending';
            } else {
                btnStatus.className = 'btn btn-success w-100 fw-bold';
                btnStatus.innerHTML = '<i class="bi bi-check-circle"></i> Selesaikan Pesanan';
            }
            
            modalDetail.show();
        }
    }

    function printReceipt() {
        const receiptHtml = document.getElementById('receiptArea').outerHTML;
        const printWindow = window.open('', '', 'width=400,height=600');
        printWindow.document.write(`
            <html>
            <head>
                <title>Print Receipt</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
                <style>body { padding: 20px; display: flex; justify-content: center; }</style>
            </head>
            <body onload="window.print(); window.close();">
                ${receiptHtml}
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
</body>
</html>
