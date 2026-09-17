<?php
session_start();
include 'koneksi.php';

// Validasi login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Ambil data tiket untuk laporan
$query = "SELECT * FROM tickets ORDER BY id_ticket DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Kerusakan - PT EPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #ffffff; color: #000; }
        .table th, .table td { font-size: 13px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container mt-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-uppercase">PT EPN Maintenance</h3>
            <h5 class="text-secondary">Laporan Rekapitulasi Kerusakan Aset / Pos Timbangan</h5>
            <p class="small text-muted">Tanggal Cetak: <?php echo date('d-m-Y H:i:s'); ?></p>
        </div>

        <div class="no-print mb-3 text-end">
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">Cetak / Simpan PDF</button>
            <a href="dashboard.php" class="btn btn-secondary btn-sm fw-bold">Kembali</a>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Aset</th>
                    <th>Deskripsi Kerusakan</th>
                    <th style="width: 100px;">Prioritas</th>
                    <th style="width: 100px;">Pelapor</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td>Pos Timbangan Abu <?php echo htmlspecialchars($row['id_asset']); ?></td>
                            <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                            <td class="text-center"><?php echo ucfirst($row['prioritas']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['pelapor']); ?></td>
                            <td class="text-center text-uppercase fw-bold"><?php echo $row['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">Belum ada data laporan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>