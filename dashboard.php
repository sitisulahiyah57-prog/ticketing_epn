<?php
session_start();
include 'koneksi.php';

// Validasi sesi login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// Ambil kata kunci pencarian jika ada
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';

// Ambil data hitung jumlah tiket berdasarkan status (tetap menghitung total keseluruhan)
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tickets");
$total_tiket = mysqli_fetch_assoc($q_total)['total'];

$q_open = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tickets WHERE status='open'");
$total_open = mysqli_fetch_assoc($q_open)['total'];

$q_prog = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tickets WHERE status='in_progress'");
$total_prog = mysqli_fetch_assoc($q_prog)['total'];

$q_sel = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tickets WHERE status='selesai'");
$total_selesai = mysqli_fetch_assoc($q_sel)['total'];

// Query data tiket dengan filter pencarian jika ada
if (!empty($keyword)) {
    $query = "SELECT * FROM tickets WHERE id_asset LIKE '%$keyword%' OR deskripsi LIKE '%$keyword%' OR pelapor LIKE '%$keyword%' OR status LIKE '%$keyword%' ORDER BY id_ticket DESC";
} else {
    $query = "SELECT * FROM tickets ORDER BY id_ticket DESC";
}
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Maintenance - PT EPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js untuk Grafik Donut -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <!-- Navbar Utama -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm px-4">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-tools me-2"></i> PT EPN Maintenance</a>
        <div class="ms-auto d-flex align-items-center text-white">
            <span class="me-3"><i class="fas fa-user-circle me-1"></i> Halo, <b><?php echo htmlspecialchars($username); ?></b> (<?php echo ucfirst($role); ?>)</span>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
        </div>
    </nav>

    <div class="container py-4">
        
        <!-- ROW 1: 4 KOTAK STATISTIK ATAS -->
        <div class="row mb-4">
            <!-- Total Tiket -->
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white" style="background-color: #0d6efd;">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1" style="font-size: 0.85rem;">Total Tiket</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_tiket; ?></h2>
                        </div>
                        <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <!-- Tiket Open -->
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white" style="background-color: #dc3545;">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1" style="font-size: 0.85rem;">Tiket Open</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_open; ?></h2>
                        </div>
                        <i class="fas fa-folder-open fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <!-- In Progress -->
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-dark" style="background-color: #ffc107;">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1" style="font-size: 0.85rem;">In Progress</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_prog; ?></h2>
                        </div>
                        <i class="fas fa-spinner fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <!-- Selesai -->
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white" style="background-color: #198754;">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1" style="font-size: 0.85rem;">Selesai</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_selesai; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: GRAFIK & MENU LAPORAN & AKSI -->
        <div class="row mb-4">
            <!-- Kotak Grafik Donut -->
            <div class="col-lg-7 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-chart-pie me-2"></i>Persentase Status Penanganan</h6>
                        <div class="row align-items-center">
                            <div class="col-md-7 text-center">
                                <div style="max-width: 220px; margin: 0 auto;">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge rounded-pill me-2" style="background-color: #dc3545; width: 12px; height: 12px; display: inline-block;"></span>
                                    <span class="small fw-semibold text-secondary">Open</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge rounded-pill me-2" style="background-color: #ffc107; width: 12px; height: 12px; display: inline-block;"></span>
                                    <span class="small fw-semibold text-secondary">In Progress</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge rounded-pill me-2" style="background-color: #198754; width: 12px; height: 12px; display: inline-block;"></span>
                                    <span class="small fw-semibold text-secondary">Selesai</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kotak Menu Laporan & Aksi -->
            <div class="col-lg-5 mb-3">
                <div class="card border-0 shadow-sm h-100 text-center p-3">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="mb-2 text-primary">
                            <i class="fas fa-file-invoice fa-3x"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Menu Laporan & Aksi</h5>
                        <p class="text-muted small mb-3">Tambahkan laporan kerusakan baru atau cetak rekapitulasi data.</p>
                        
                        <?php if ($role == 'operator' || $role == 'admin'): ?>
                            <!-- Tombol Buat Tiket Baru -->
                            <a href="buat-tiket.php" class="btn btn-primary fw-bold mb-2 py-2 shadow-sm">
                                <i class="fas fa-plus me-1"></i> Buat Tiket Baru
                            </a>
                        <?php endif; ?>
                        
                        <div class="d-flex gap-2 justify-content-center">
                            <!-- Tombol Cetak Laporan -->
                            <a href="cetak-laporan.php" target="_blank" class="btn btn-outline-dark btn-sm w-50 fw-semibold">
                                <i class="fas fa-file-pdf text-danger me-1"></i> Cetak Laporan
                            </a>
                            <a href="export-excel.php" class="btn btn-outline-dark btn-sm w-50 fw-semibold">
                                <i class="fas fa-file-excel text-success me-1"></i> Unduh Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3: TABEL DAFTAR LAPORAN KERUSAKAN & PENCARIAN -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2"></i>Daftar Laporan Kerusakan</h5>
                
                <!-- Form Pencarian -->
                <form action="" method="GET" class="d-flex" style="width: 100%; max-width: 320px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="cari" class="form-control" placeholder="Cari aset, pelapor, status..." value="<?php echo htmlspecialchars($keyword); ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        <?php if (!empty($keyword)): ?>
                            <a href="dashboard.php" class="btn btn-outline-secondary" title="Reset Pencarian"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 ps-3" style="width: 60px;">No</th>
                                <th class="py-3" style="width: 200px;">Aset / Perangkat</th>
                                <th class="py-3">Deskripsi Kerusakan</th>
                                <th class="py-3 text-center" style="width: 110px;">Prioritas</th>
                                <th class="py-3 text-center" style="width: 110px;">Pelapor</th>
                                <th class="py-3 text-center" style="width: 110px;">Status</th>
                                <th class="py-3 text-center" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-secondary"><?php echo $no++; ?></td>
                                        <td class="fw-semibold">Pos Timbangan Abu <?php echo htmlspecialchars($row['id_asset']); ?></td>
                                        <td class="text-muted small"><?php echo htmlspecialchars(isset($row['deskripsi']) ? $row['deskripsi'] : '-'); ?></td>
                                        <td class="text-center">
                                            <?php 
                                                $prioritas = strtolower($row['prioritas']);
                                                $badge_prio = 'bg-secondary';
                                                if ($prioritas == 'tinggi' || $prioritas == 'high') $badge_prio = 'bg-danger';
                                                elseif ($prioritas == 'sedang' || $prioritas == 'medium') $badge_prio = 'bg-warning text-dark';
                                                elseif ($prioritas == 'rendah' || $prioritas == 'low') $badge_prio = 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badge_prio; ?>"><?php echo ucfirst($row['prioritas']); ?></span>
                                        </td>
                                        <td class="text-center small"><?php echo htmlspecialchars(isset($row['pelapor']) ? $row['pelapor'] : 'Operator'); ?></td>
                                        <td class="text-center">
                                            <?php 
                                                $status = $row['status'];
                                                $badge_status = 'bg-secondary';
                                                if ($status == 'open') $badge_status = 'bg-danger';
                                                elseif ($status == 'in_progress') $badge_status = 'bg-warning text-dark';
                                                elseif ($status == 'selesai') $badge_status = 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badge_status; ?>"><?php echo strtoupper($status); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="detail.php?id=<?php echo $row['id_ticket']; ?>" class="btn btn-sm btn-primary text-white fw-bold px-2 py-1" title="Lihat Detail">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <?php if (!empty($keyword)): ?>
                                            Tidak ditemukan data yang sesuai dengan kata kunci "<b><?php echo htmlspecialchars($keyword); ?></b>".
                                        <?php else: ?>
                                            Belum ada data laporan kerusakan yang tersedia.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Script Render Chart Donut -->
    <script>
        const ctx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Selesai'],
                datasets: [{
                    data: [<?php echo $total_open; ?>, <?php echo $total_prog; ?>, <?php echo $total_selesai; ?>],
                    backgroundColor: ['#dc3545', '#ffc107', '#198754'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>