<?php
include 'koneksi.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_ETicketing_PT_EPN.xls");
?>

<h3 style="text-align: center;">Laporan Pemeliharaan E-Ticketing Pos Abu 1-7</h3>
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>No</th>
            <th>ID Tiket</th>
            <th>Kendala</th>
            <th>Status</th>
            <th>Tanggal Lapor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $data = mysqli_query($koneksi, "SELECT * FROM tickets");
        while($row = mysqli_fetch_assoc($data)){
            echo "<tr>
                <td>{$no}</td>
                <td>{$row['id_ticket']}</td>
                <td>{$row['deskripsi_kendala']}</td>
                <td>{$row['status']}</td>
                <td>{$row['tgl_lapor']}</td>
            </tr>";
            $no++;
        }
        ?>
    </tbody>
</table>