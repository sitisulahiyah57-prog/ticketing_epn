<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$id_ticket = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Proses update status jika form dikirim oleh teknisi/admin
if (isset($_POST['update_status']) && ($role == 'teknisi' || $role == 'admin')) {
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $update_query = "UPDATE tickets SET status = '$status_baru' WHERE id_ticket = $id_ticket";
    if (mysqli_query($koneksi, $update_query)) {
        header("Location: detail.php?id=" . $id_ticket . "&pesan=sukses");
        exit;
    }
}

$query = mysqli_query($koneksi, "SELECT * FROM tickets WHERE id_ticket = $id_ticket");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: dashboard.php");
    exit;
}

// Data email tujuan (diubah ke Teknisi)
$email_teknisi = "teknisi@pt-epn.co.id";
$email_pimpinan = "pimpinan@pt-epn.co.id";

// Format teks untuk dikirim via email (mailto)
$subjek_email = rawurlencode("Laporan Kerusakan Aset - Pos Timbangan " . $data['id_asset']);
$body_email = rawurlencode(
    "Kepada Yth. Tim Teknisi / Pimpinan,\n\n" .
    "Berikut adalah laporan kerusakan perangkat/aset yang memerlukan tindakan:\n\n" .
    "- ID Tiket: #" . $data['id_ticket'] . "\n" .
    "- Aset: Pos Timbangan " . $data['id_asset'] . "\n" .
    "- Deskripsi: " . $data['deskripsi'] . "\n" .
    "- Prioritas: " . ucfirst($data['prioritas']) . "\n" .
    "- Status: " . strtoupper($data['status']) . "\n" .
    "- Pelapor: " . (isset($data['pelapor']) ? $data['pelapor'] : 'Operator') . "\n\n" .
    "Mohon tindak lanjutnya. Terima kasih."
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Tiket #<?php echo $data['id_ticket']; ?> - PT EPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width: 750px;">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Detail Tiket Kerusakan #<?php echo $data['id_ticket']; ?></h5>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">Kembali</a>
            </div>
            <div class="card-body p-4">
                
                <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> Status progres berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-secondary">Aset / Perangkat</div>
                    <div class="col-md-8">Pos Timbangan Abu <?php echo htmlspecialchars($data['id_asset']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-secondary">Pelapor</div>
                    <div class="col-md-8"><?php echo htmlspecialchars(isset($data['pelapor']) ? $data['pelapor'] : 'Operator'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-secondary">Prioritas</div>
                    <div class="col-md-8">
                        <?php 
                            $prio = strtolower($data['prioritas']);
                            $bg_prio = ($prio == 'tinggi' || $prio == 'high') ? 'bg-danger' : (($prio == 'sedang') ? 'bg-warning text-dark' : 'bg-success');
                        ?>
                        <span class="badge <?php echo $bg_prio; ?>"><?php echo ucfirst($data['prioritas']); ?></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-secondary">Status Saat Ini</div>
                    <div class="col-md-8">
                        <?php 
                            $st = $data['status'];
                            $bg_st = ($st == 'open') ? 'bg-danger' : (($st == 'in_progress') ? 'bg-warning text-dark' : 'bg-success');
                        ?>
                        <span class="badge <?php echo $bg_st; ?> fs-6"><?php echo strtoupper($st); ?></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-secondary">Deskripsi Kerusakan</div>
                    <div class="col-md-8 p-3 bg-light rounded border"><?php echo nl2br(htmlspecialchars($data['deskripsi'])); ?></div>
                </div>

                <!-- Bagian Foto Dokumentasi -->
                <div class="row mb-4">
                    <div class="col-md-4 fw-semibold text-secondary">Foto Dokumentasi</div>
                    <div class="col-md-8">
                        <?php if (!empty($data['foto']) && file_exists('uploads/' . $data['foto'])): ?>
                            <a href="uploads/<?php echo $data['foto']; ?>" target="_blank">
                                <img src="uploads/<?php echo $data['foto']; ?>" alt="Foto Kerusakan" class="img-thumbnail rounded" style="max-height: 220px;">
                            </a>
                            <div class="form-text">Klik gambar untuk memperbesar.</div>
                        <?php else: ?>
                            <span class="text-muted fst-italic">Tidak ada foto dokumentasi yang dilampirkan.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FORM UPDATE STATUS KHUSUS TEKNISI / ADMIN -->
                <?php if ($role == 'teknisi' || $role == 'admin'): ?>
                    <hr class="my-4">
                    <div class="p-3 bg-white border rounded shadow-sm mb-4">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-tools me-2"></i>Ubah Progres / Status Pengerjaan</h6>
                        <form action="" method="POST" class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <select name="status" class="form-select" required>
                                    <option value="open" <?php if($data['status'] == 'open') echo 'selected'; ?>>Open (Belum Ditangani)</option>
                                    <option value="in_progress" <?php if($data['status'] == 'in_progress') echo 'selected'; ?>>In Progress (Sedang Dikerjakan)</option>
                                    <option value="selesai" <?php if($data['status'] == 'selesai') echo 'selected'; ?>>Selesai (Perbaikan Selesai)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="update_status" class="btn btn-success w-100 fw-bold">
                                    <i class="fas fa-save me-1"></i> Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <hr class="my-4">

                <!-- Tombol Kirim Laporan via Email ke Pimpinan / Teknisi -->
                <div class="p-3 bg-light border rounded text-center">
                    <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-paper-plane me-2 text-primary"></i>Kirim Laporan Ini ke Email</h6>
                    <p class="small text-muted mb-3">Klik tombol di bawah untuk membuka aplikasi email (Outlook/Gmail) dan mengirimkan laporan otomatis ke pihak terkait.</p>
                    
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="mailto:<?php echo $email_teknisi; ?>?subject=<?php echo $subjek_email; ?>&body=<?php echo $body_email; ?>" class="btn btn-primary btn-sm fw-semibold">
                            <i class="fas fa-envelope me-1"></i> Kirim ke Teknisi (<?php echo $email_teknisi; ?>)
                        </a>
                        <a href="mailto:<?php echo $email_pimpinan; ?>?subject=<?php echo $subjek_email; ?>&body=<?php echo $body_email; ?>" class="btn btn-outline-dark btn-sm fw-semibold">
                            <i class="fas fa-envelope me-1"></i> Kirim ke Pimpinan (<?php echo $email_pimpinan; ?>)
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>