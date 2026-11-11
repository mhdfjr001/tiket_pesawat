<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_tiket";

$conn = mysqli_connect($host, $user, $pass, $db);

// cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// set charset (biar aman)
mysqli_set_charset($conn, "utf8");
?>