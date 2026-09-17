<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']);

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama']    = $data['nama'];
        $_SESSION['role']    = $data['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Maintenance EPN Abu 1-7</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-center fw-bold mb-1">E-Ticketing EPN</h4>
                    <p class="text-center text-muted small mb-4">Sistem Maintenance Perangkat Abu 1–7</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Masuk Sistem</button>
                    </form>
                    <div class="mt-4 text-center">
                        <small class="text-muted">Gunakan: <b>operator</b> / <b>teknisi</b> (Pass: 12345)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>