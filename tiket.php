<?php
include_once 'config.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

$pesan_sukses = "";
$pesan_error = "";

// 1. Tangkap ID Tiket
$id_ticket = 0;
if (isset($_GET['id'])) {
    $id_ticket = intval($_GET['id']);
} elseif (isset($_POST['id_ticket'])) {
    $id_ticket = intval($_POST['id_ticket']);
}

// 2. Eksekusi Update Status & Catatan Solusi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status_baru    = $_POST['status'];
    $catatan_solusi = mysqli_real_escape_string($koneksi, $_POST['catatan_solusi']);
    
    $update_sql = "UPDATE tickets SET status = '$status_baru', catatan_solusi = '$catatan_solusi' WHERE id_ticket = '$id_ticket'";
    $exec = mysqli_query($koneksi, $update_sql);
    
    if ($exec) {
        $pesan_sukses = "Status dan catatan perbaikan berhasil diperbarui di database!";
    } else {
        $pesan_error = "Error MySQL: " . mysqli_error($koneksi);
    }
}

// 3. Ambil Data Tiket Terbaru
$query = mysqli_query($koneksi, "SELECT * FROM tickets WHERE id_ticket = '$id_ticket'");
$ticket = mysqli_fetch_assoc($query);

if (!$ticket) {
    echo "<div class='container mt-5 text-center'><h3>Tiket #$id_ticket tidak ditemukan!</h3><a href='dashboard.php' class='btn btn-primary mt-3'>Kembali ke Dashboard</a></div>";
    exit();
}

$nama_asset = "Pos Timbangan Abu 1";
if (!empty($ticket['id_asset'])) {
    $id_a = $ticket['id_asset'];
    $q_a = mysqli_query($koneksi, "SELECT * FROM assets WHERE id_asset = '$id_a'");
    if ($q_a && $d_a = mysqli_fetch_assoc($q_a)) {
        $nama_asset = $d_a['nama_asset'] ?? $nama_asset;
    }
}

$nama_pelapor = "Operator";
if (!empty($ticket['id_reporter'])) {
    $id_u = $ticket['id_reporter'];
    $q_u = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_u'");
    if ($q_u && $d_u = mysqli_fetch_assoc($q_u)) {
        $nama_pelapor = $d_u['nama_lengkap'] ?? ($d_u['username'] ?? $nama_pelapor);
    }
}

$current_status = strtolower(trim($ticket['status'] ?? 'open'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tiket #<?= $id_ticket ?> - PT EPN Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">PT EPN Maintenance</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $pesan_sukses ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $pesan_error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Detail Tiket -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-ticket me-2"></i>Detail Tiket #<?= $id_ticket ?></h5>
                        <span class="badge bg-primary px-3 py-2 fs-6"><?= strtoupper($current_status) ?></span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted fw-semibold">Perangkat / Aset</div>
                            <div class="col-md-8 fw-bold text-primary"><?= htmlspecialchars($nama_asset) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted fw-semibold">Pelapor</div>
                            <div class="col-md-8"><?= htmlspecialchars($nama_pelapor) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted fw-semibold">Deskripsi Kerusakan</div>
                            <div class="col-md-8"><?= nl2br(htmlspecialchars($ticket['deskripsi'] ?? ($ticket['deskripsi_kendala'] ?? '-'))) ?></div>
                        </div>
                        <?php if(!empty($ticket['catatan_solusi'])): ?>
                            <hr>
                            <div class="row mb-2">
                                <div class="col-md-4 text-muted fw-semibold">Catatan Solusi Perbaikan</div>
                                <div class="col-md-8 text-success fw-bold"><?= nl2br(htmlspecialchars($ticket['catatan_solusi'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Update Status & Solusi -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Update Penanganan Teknisi
                    </div>
                    <div class="card-body p-4">
                        <form action="tiket.php?id=<?= $id_ticket ?>" method="POST">
                            <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Status Baru</label>
                                <select name="status" class="form-select form-select-lg" required>
                                    <option value="open" <?= ($current_status == 'open') ? 'selected' : '' ?>>Open (Belum Ditangani)</option>
                                    <option value="in_progress" <?= ($current_status == 'in_progress') ? 'selected' : '' ?>>In Progress (Sedang Diperbaiki)</option>
                                    <option value="resolved" <?= ($current_status == 'resolved') ? 'selected' : '' ?>>Resolved (Selesai Perbaikan)</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Catatan Solusi / Tindakan Perbaikan</label>
                                <textarea name="catatan_solusi" class="form-control" rows="3" placeholder="Tuliskan tindakan teknisi di sini... (contoh: Sensor dibersihkan dan kalibrasi ulang konektor port 2)"><?= htmlspecialchars($ticket['catatan_solusi'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" name="update_status" class="btn btn-success btn-lg w-100 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>