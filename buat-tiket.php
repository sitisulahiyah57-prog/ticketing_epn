<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'operator' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit;
}

$error = "";

// Ambil daftar aset dari tabel assets untuk pilihan dropdown
$query_assets = mysqli_query($koneksi, "SELECT * FROM assets");

if (isset($_POST['submit'])) {
    $id_asset = mysqli_real_escape_string($koneksi, $_POST['id_asset']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $prioritas = mysqli_real_escape_string($koneksi, $_POST['prioritas']);
    $pelapor = isset($_SESSION['username']) ? $_SESSION['username'] : 'Operator';
    $status = 'open';

    // Handle Upload Foto
    $nama_foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = $_FILES['foto']['name'];
        $ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $nama_foto = 'foto_' . time() . '.' . $ekstensi;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            move_uploaded_file($file_tmp, 'uploads/' . $nama_foto);
        } else {
            $error = "Ekstensi file foto harus JPG, JPEG, PNG, atau WEBP!";
        }
    }

    if (empty($error)) {
        // Menyimpan data dengan menyesuaikan kolom pelapor
        $query = "INSERT INTO tickets (id_asset, deskripsi, prioritas, pelapor, status, foto) 
                  VALUES ('$id_asset', '$deskripsi', '$prioritas', '$pelapor', '$status', '$nama_foto')";
        
        if (mysqli_query($koneksi, $query)) {
            header("Location: dashboard.php?status=sukses");
            exit;
        } else {
            $error = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Tiket Kerusakan - PT EPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width: 600px;">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Form Buat Laporan Kerusakan</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Aset / Pos Timbangan</label>
                        <select name="id_asset" class="form-select" required>
                            <option value="">-- Pilih Aset Terdaftar --</option>
                            <?php while ($row_asset = mysqli_fetch_assoc($query_assets)): ?>
                                <option value="<?php echo $row_asset['id_asset']; ?>">
                                    <?php echo "Pos Timbangan " . htmlspecialchars($row_asset['id_asset']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Kerusakan</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan detail kerusakan perangkat..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prioritas</label>
                        <select name="prioritas" class="form-select" required>
                            <option value="rendah">Rendah (Low)</option>
                            <option value="sedang" selected>Sedang (Medium)</option>
                            <option value="tinggi">Tinggi (High / Urgent)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Foto Dokumentasi Kerusakan <span class="text-muted small">(Opsional)</span></label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <div class="form-text">Format yang diizinkan: JPG, JPEG, PNG, WEBP.</div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary px-4 fw-semibold">Kembali</a>
                        <button type="submit" name="submit" class="btn btn-primary px-4 fw-bold">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>