<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_maintenance_epn"; // Sesuaikan nama database Anda

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!mysqli_connect_errno()) {
    // Koneksi berhasil
} else {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>