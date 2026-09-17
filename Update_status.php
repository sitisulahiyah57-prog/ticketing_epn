<?php
session_start();
include 'koneksi.php';

// Validasi hak akses (hanya teknisi dan pimpinan yang boleh ubah status)
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'teknisi' && $_SESSION['role'] != 'pimpinan')) {
    echo "<script>alert('Anda tidak memiliki hak akses untuk mengubah status!'); window.location='dashboard.php';</script>";
    exit;
}

// Ambil ID dari URL
$id_ticket = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Jika tombol Simpan diklik
if (isset($_POST['update_status'])) {
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $update = mysqli_query($koneksi, "UPDATE tickets SET status = '$status_baru' WHERE id_ticket = '$id_ticket'");
    
    if ($update) {
        echo "<script>alert('Status tiket berhasil diperbarui!'); window.location='dashboard.php';</script>";
        exit;
    } else {
        $error = "Gagal memperbarui status: " . mysqli_error($koneksi);
    }
}

// Ambil data tiket saat ini
$query = mysqli_query($koneksi, "SELECT * FROM tickets WHERE id_ticket = '$id_ticket'");
$ticket = mysqli_fetch_assoc($query);

if (!$ticket) {
    echo "<script>alert('Data tiket tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Status Tiket #<?php echo $ticket['id_ticket']; ?> - PT EPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm px-4">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-tools me-2"></i> PT EPN Maintenance</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-edit me-2 text-warning"></i> Ubah Status Tiket #<?php echo $ticket['id_ticket']; ?></h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID Aset / Perangkat</label>
                                <input type="text" class="form-control" value="Pos Timbangan Abu <?php echo $ticket['id_asset']; ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kendala</label>
                                <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($ticket['deskripsi_kendala']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Status Baru</label>
                                <select name="status" class="form-select" required>
                                    <option value="open" <?php if($ticket['status'] == 'open') echo 'selected'; ?>>Open (Belum Ditangani)</option>
                                    <option value="in_progress" <?php if($ticket['status'] == 'in_progress') echo 'selected'; ?>>In Progress (Sedang Dikerjakan)</option>
                                    <option value="selesai" <?php if($ticket['status'] == 'selesai') echo 'selected'; ?>>Selesai (Perbaikan Tuntas)</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Batal</a>
                                <button type="submit" name="update_status" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>