<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Jika tombol login ditekan
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);
        if (password_verify($password, $data['password'])) {
            $_SESSION['login'] = true;
            error_log('LOGIN SUCCESS - session login=' . ($_SESSION['login'] ?? 'null'));
            header("Location: ../index.php");
            exit;
        }
    }
    // Jika gagal, buat variabel error
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login E-Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #2c2420; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .btn-login {
            background-color: #3e2723;
            color: white;
            width: 100%;
            padding: 10px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #5d4037;
        }
        /* Styling tambahan agar input grup terlihat profesional */
        .input-group-text {
            background-color: #f8f9fa;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="mb-3" style="font-size: 50px;">☕</div>
        <h4 class="fw-bold">Selamat Datang</h4>
        <p class="text-muted">Silakan masukkan akun kasir Anda</p>
    </div>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label text-secondary">Username</label>
            <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" required>
        </div>
        
        <div class="mb-4">
            <label class="form-label text-secondary">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Masukkan password" required>
                <span class="input-group-text" id="togglePassword">
                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                </span>
            </div>
        </div>
        
        <button type="submit" name="login" class="btn btn-login btn-lg">Login</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const toggleIcon = document.querySelector('#toggleIcon');

    togglePassword.addEventListener('click', function () {
        // Toggle tipe input
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle ikon Bootstrap
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });
</script>
</body>
</html>