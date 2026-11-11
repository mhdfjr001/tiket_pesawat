<?php
session_start();
require "config/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// aksi terima/tolak
if (isset($_GET['terima'])) {
    mysqli_query($conn, "UPDATE transaksi SET status='diterima' WHERE id='$_GET[terima]'");
    header("Location: admin.php");
}
if (isset($_GET['tolak'])) {
    mysqli_query($conn, "UPDATE transaksi SET status='ditolak' WHERE id='$_GET[tolak]'");
    header("Location: admin.php");
}

// statistik
$t1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM transaksi"));
$t2 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM transaksi WHERE status='pending'"));
$t3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM transaksi WHERE status='diterima'"));
$t4 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM transaksi WHERE status='ditolak'"));

$hasil = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(total) as pemasukan FROM transaksi WHERE status='diterima'
"));

// data transaksi
$data = mysqli_query($conn,"
SELECT transaksi.*, users.username 
FROM transaksi 
JOIN users ON transaksi.user_id = users.id 
ORDER BY transaksi.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Panel</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f1f5f9;
        margin: 0;
    }

    .navbar {
        background: #0f172a;
        color: white;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        margin-left: 15px;
    }

    .container {
        padding: 25px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .card {
        padding: 20px;
        border-radius: 12px;
        color: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .total { background: #334155; }
    .pending { background: #f59e0b; }
    .ok { background: #22c55e; }
    .no { background: #ef4444; }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .table th {
        background: #0f172a;
        color: white;
        padding: 12px;
        text-align: left;
    }

    .table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .table tr:hover {
        background: #f8fafc;
    }

    .btn {
        padding: 6px 12px;
        border-radius: 6px;
        color: white;
        text-decoration: none;
        font-size: 12px;
        margin: 0 3px;
        display: inline-block;
    }

    .ok-btn { background: #22c55e; }
    .no-btn { background: #ef4444; }

    .btn:hover {
        opacity: 0.8;
    }

    .status-pending {
        background: #fef3c7;
        color: #d97706;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-diterima {
        background: #d1fae5;
        color: #059669;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-ditolak {
        background: #fee2e2;
        color: #dc2626;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="navbar">
    <div><b>✈️ ADMIN DASHBOARD</b></div>
    <div>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Total Pemasukan: Rp <?= number_format($hasil['pemasukan'] ?? 0, 0, ',', '.') ?></h2>

    <div class="cards">
        <div class="card total">
            <small>Total Transaksi</small>
            <h2><?= $t1['total'] ?></h2>
        </div>
        <div class="card pending">
            <small>Pending</small>
            <h2><?= $t2['total'] ?></h2>
        </div>
        <div class="card ok">
            <small>Diterima</small>
            <h2><?= $t3['total'] ?></h2>
        </div>
        <div class="card no">
            <small>Ditolak</small>
            <h2><?= $t4['total'] ?></h2>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($d = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['username']) ?></td>
                <td><?= $d['jumlah'] ?> tiket</td>
                <td>Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                <td><?= date('d/m/Y H:i', strtotime($d['created_at'] ?? $d['tanggal'] ?? 'now')) ?></td>
                <td>
                    <span class="status-<?= $d['status'] ?>">
                        <?= ucfirst($d['status']) ?>
                    </span>
                 </td>
                <td>
                    <?php if ($d['status'] == 'pending') { ?>
                        <a class="btn ok-btn" href="?terima=<?= $d['id'] ?>" onclick="return confirm('Terima pesanan ini?')">Terima</a>
                        <a class="btn no-btn" href="?tolak=<?= $d['id'] ?>" onclick="return confirm('Tolak pesanan ini?')">Tolak</a>
                    <?php } else { ?>
                        <span style="color:#94a3b8;">-</span>
                    <?php } ?>
                 </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>