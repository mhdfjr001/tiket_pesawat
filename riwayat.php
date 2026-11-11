<?php
session_start();
require "config/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek role user
if ($_SESSION['role'] != 'user') {
    echo "Akses ditolak! Halaman ini hanya untuk user biasa.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Query ambil riwayat transaksi user - sesuai struktur database
$data = mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE user_id = '$user_id' 
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pemesanan Tiket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar .logo {
            font-size: 18px;
            font-weight: bold;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 5px 10px;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .navbar a:hover {
            background: #334155;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 11px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .page-header h2 {
            color: #0f172a;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #64748b;
            font-size: 14px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 11px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #64748b;
            font-size: 14px;
        }

        .table-wrapper {
            background: white;
            border-radius: 11px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .table th {
            background: #0f172a;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            min-width: 80px;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-diterima {
            background: #d1fae5;
            color: #059669;
        }

        .status-ditolak {
            background: #fee2e2;
            color: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 11px;
        }

        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #0f172a;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 20px;
        }

        .btn-primary {
            display: inline-block;
            padding: 10px 20px;
            background: #0f172a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #1e293b;
        }

        .btn-detail {
            padding: 5px 12px;
            background: #64748b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
        }

        .btn-detail:hover {
            background: #475569;
        }

        @media (max-width: 768px) {
            .table {
                font-size: 12px;
            }
            
            .table th, 
            .table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        ✈️ <span>TiketPesawat</span>.com
    </div>
    <div>
        <a href="index.php">🏠 Beranda</a>
        <a href="riwayat.php">📋 Riwayat</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h2>📋 Riwayat Pemesanan Tiket</h2>
        <p>Berikut adalah daftar semua pemesanan tiket yang telah Anda lakukan</p>
    </div>

    <?php
    // Hitung statistik
    $total_transaksi = mysqli_num_rows($data);
    $total_nominal = 0;
    $pending = 0;
    $diterima = 0;
    $ditolak = 0;
    
    // Reset data pointer untuk statistik
    if ($total_transaksi > 0) {
        mysqli_data_seek($data, 0);
        while($stat = mysqli_fetch_assoc($data)) {
            $total_nominal += $stat['total'];
            if($stat['status'] == 'pending') $pending++;
            if($stat['status'] == 'diterima') $diterima++;
            if($stat['status'] == 'ditolak') $ditolak++;
        }
        // Reset lagi untuk tampilan tabel
        mysqli_data_seek($data, 0);
    }
    ?>

    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $total_transaksi ?></div>
            <div class="label">Total Transaksi</div>
        </div>
        <div class="stat-card">
            <div class="number">Rp <?= number_format($total_nominal, 0, ',', '.') ?></div>
            <div class="label">Total Nominal</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color: #f59e0b;"><?= $pending ?></div>
            <div class="label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color: #22c55e;"><?= $diterima ?></div>
            <div class="label">Diterima</div>
        </div>
    </div>

    <div class="table-wrapper">
        <?php if ($total_transaksi > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Jumlah Tiket</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                        <?php
                        // Gunakan kolom tanggal atau created_at
                        $tanggal_transaksi = isset($d['tanggal']) && $d['tanggal'] != '0000-00-00 00:00:00' 
                                   ? date('d/m/Y H:i', strtotime($d['tanggal'])) 
                                   : (isset($d['created_at']) ? date('d/m/Y H:i', strtotime($d['created_at'])) : date('d/m/Y H:i'));
                        ?>
                        <tr>
                            <td>#<?= $d['id'] ?></td>
                            <td><?= $tanggal_transaksi ?></td>
                            <td><?= $d['jumlah'] ?> tiket</td>
                            <td>Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                            <td>
                                <span class="status-badge status-<?= $d['status'] ?>">
                                    <?= ucfirst($d['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_transaksi.php?id=<?= $d['id'] ?>" class="btn-detail">Detail</a>
                            </td>
                         </td>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">🎫</div>
                <h3>Belum Ada Pemesanan</h3>
                <p>Anda belum melakukan pemesanan tiket apapun.</p>
                <a href="index.php" class="btn-primary">✈️ Pesan Tiket Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>