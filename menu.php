<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login/login.php");
    exit;
}
require __DIR__ . '/config/koneksi.php';

// Proses Tambah Menu
if (isset($_POST['add_menu'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    // Default image jika tidak ada upload
    $gambar = "https://images.unsplash.com/photo-1541167760496-1628856ab772?w=400&q=80";
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target_path = 'image/' . $filename;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_path)) {
            $gambar = $target_path;
        }
    }
    
    mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, stok, gambar) VALUES ('$nama', $harga, $stok, '$gambar')");
    header("Location: menu.php");
    exit;
}

// Proses Edit Stok
if (isset($_POST['edit_menu'])) {
    $id = (int)$_POST['id_produk'];
    $stok = (int)$_POST['stok'];
    mysqli_query($koneksi, "UPDATE produk SET stok = $stok WHERE id_produk = $id");
    header("Location: menu.php");
    exit;
}

// Proses Hapus Menu
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk = $id");
    header("Location: menu.php");
    exit;
}

// Ambil semua data produk untuk tabel dan statistik
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
$semua_produk = [];
$total_menu = 0;
$hampir_habis = 0;
$habis = 0;

while($row = mysqli_fetch_assoc($query_produk)) {
    $semua_produk[] = $row;
    $total_menu++;
    if ((int)$row['stok'] <= 0) {
        $habis++;
    } elseif ((int)$row['stok'] <= 10) {
        $hampir_habis++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - E-Cafe POS</title>
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
        
        /* Sidebar (Same as index.php) */
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
        
        .btn-add-menu {
            background-color: #342820;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-add-menu:hover {
            background-color: #48382d;
            color: white;
        }
        
        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
        }
        
        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .icon-orange { background: #ffe0b2; color: #e65100; }
        .icon-red { background: #ffcdd2; color: #c62828; }
        
        .summary-info h6 {
            color: #666;
            font-size: 13px;
            margin: 0 0 4px 0;
        }
        
        .summary-info h3 {
            color: var(--text-dark);
            font-weight: 700;
            margin: 0;
            font-size: 24px;
        }
        
        /* Table Controls */
        .table-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .search-input {
            flex: 1;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-input input {
            border: none;
            outline: none;
            width: 100%;
            background: transparent;
        }
        
        .search-input i {
            color: #888;
        }
        
        .filter-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 15px;
            background: white;
            color: #555;
            outline: none;
            cursor: pointer;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            font-size: 11px;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #fdfdfd;
        }
        
        .table td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            color: #444;
            font-size: 14px;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .menu-item-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .menu-item-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .menu-item-name {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-tersedia { background: #e3f2fd; color: #1565c0; }
        .badge-habis { background: #ffebee; color: #c62828; }
        
        .action-icons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            border: none;
            background: none;
            color: #888;
            padding: 5px;
            cursor: pointer;
            transition: 0.2s;
        }
        
        .action-btn:hover { color: var(--text-dark); }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
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
        
        .page-item:hover {
            background: #f0f0f0;
        }
        
        .page-item.active {
            background: #342820;
            color: white;
        }
        
        /* Modals Customize */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 18px;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            background: #fdfdfd;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 14px;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: none;
            border-color: #342820;
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            margin-bottom: 1.5rem;
        }
        
        .upload-icon {
            background: #eee;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px auto;
            color: #888;
        }
        
        .btn-modal-primary {
            background: #342820;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-modal-primary:hover { background: #48382d; }
        
        .btn-modal-danger {
            background: #c62828;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-modal-danger:hover { background: #b71c1c; }
        
        .btn-modal-secondary {
            background: white;
            color: #555;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-modal-secondary:hover { background: #f0f0f0; }

        .delete-warning-icon {
            width: 60px;
            height: 60px;
            background: #ffebee;
            color: #c62828;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 15px auto;
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
            <a href="index.php" class="nav-link">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="menu.php" class="nav-link active">
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
        <div class="header-actions">
            <h2 class="page-title">Manajemen Menu</h2>
            <button class="btn-add-menu" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg"></i> Tambah Menu Baru
            </button>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon" style="background: #e3f2fd; color: #1565c0;"><i class="bi bi-cup-hot-fill"></i></div>
                <div class="summary-info">
                    <h6>Total Menu</h6>
                    <h3><?= $total_menu ?></h3>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon icon-orange"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="summary-info">
                    <h6>Menu Hampir Habis</h6>
                    <h3><?= $hampir_habis ?></h3>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon icon-red"><i class="bi bi-exclamation-circle-fill"></i></div>
                <div class="summary-info">
                    <h6>Menu Habis</h6>
                    <h3><?= $habis ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <table class="table table-borderless table-hover">
                <thead>
                    <tr>
                        <th width="8%">ID</th>
                        <th width="45%">NAMA MENU</th>
                        <th width="20%">HARGA</th>
                        <th width="15%">STATUS STOK</th>
                        <th width="12%" class="text-end">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($semua_produk as $row): 
                        $status_class = $row['stok'] > 0 ? 'badge-tersedia' : 'badge-habis';
                        $status_text = $row['stok'] > 0 ? 'Tersedia' : 'Habis';
                        $img = $row['gambar'] ?: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=100&q=80';
                    ?>
                    <tr>
                        <td class="text-muted">#M-<?= str_pad($row['id_produk'], 2, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <div class="menu-item-info">
                                <img src="<?= $img ?>" alt="Menu" class="menu-item-img" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                                <span class="menu-item-name"><?= htmlspecialchars($row['nama_produk']) ?></span>
                            </div>
                        </td>
                        <td class="fw-semibold">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><span class="badge-status <?= $status_class ?>"><?= $status_text ?></span></td>
                        <td class="text-end">
                            <div class="action-icons justify-content-end">
                                <button class="action-btn" onclick="openEditModal(<?= $row['id_produk'] ?>, <?= $row['stok'] ?>)"><i class="bi bi-pencil-fill"></i></button>
                                <a href="menu.php?delete=<?= $row['id_produk'] ?>" class="action-btn" onclick="return confirm('Yakin ingin menghapus menu ini?')"><i class="bi bi-trash-fill"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="pagination-container">
                <div>Menampilkan <?= $total_menu ?> menu</div>
                <div class="pagination">
                    <a href="#" class="page-item"><i class="bi bi-chevron-left"></i></a>
                    <a href="#" class="page-item active">1</a>
                    <a href="#" class="page-item"><i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal Tambah Menu -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="" enctype="multipart/form-data">
      <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Tambah Menu Baru</h5>
            <p class="text-muted small mb-0">Lengkapi detail menu di bawah ini untuk menambahkan item baru ke cafe.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Upload Foto Menu</label>
            <input type="file" class="form-control" name="gambar" accept="image/*">
            <small class="text-muted">Format .JPG/.PNG. Dikosongkan jika tidak ada.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Menu</label>
            <input type="text" class="form-control" name="nama" placeholder="Contoh: Caramel Macchiato" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Harga</label>
            <div class="input-group">
                <span class="input-group-text bg-white">Rp</span>
                <input type="number" class="form-control" name="harga" placeholder="0" required>
            </div>
        </div>
        
        <div>
            <label class="form-label">Jumlah Stok Awal</label>
            <input type="number" class="form-control" name="stok" placeholder="Contoh: 50" required>
        </div>
      </div>
      <div class="modal-footer justify-content-end">
        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" name="add_menu" class="btn-modal-primary">Tambah Menu</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Menu -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <form class="modal-content" method="POST" action="">
      <input type="hidden" name="id_produk" id="edit_id">
      <div class="modal-header">
        <h5 class="modal-title">Edit Stok Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Ubah Stok Saat Ini</label>
            <input type="number" class="form-control" name="stok" id="edit_stok" required>
        </div>
      </div>
      <div class="modal-footer justify-content-end">
        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal" style="border:none">Batal</button>
        <button type="submit" name="edit_menu" class="btn-modal-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title w-100 text-center position-relative mt-2">
            Konfirmasi Hapus
            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Close" style="margin-top:-5px;"></button>
        </h5>
      </div>
      <div class="modal-body text-center pt-4 pb-4">
        <div class="delete-warning-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <p class="text-muted mb-0">Apakah Anda yakin ingin menghapus menu ini?</p>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal" style="border:none">Batal</button>
        <button type="button" class="btn-modal-danger">Hapus</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
    function openEditModal(id, stok) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_stok').value = stok;
        editModal.show();
    }
</script>
</body>
</html>
