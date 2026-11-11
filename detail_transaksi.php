<?php
session_start();
require "config/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

$id_transaksi = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE id = '$id_transaksi' AND user_id = '$user_id'
");

$transaksi = mysqli_fetch_assoc($query);

if (!$transaksi) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location='riwayat.php';</script>";
    exit;
}

// Format tanggal - gunakan kolom yang ada di database
$tanggal = '';
if (isset($transaksi['tanggal']) && $transaksi['tanggal'] != '0000-00-00 00:00:00' && $transaksi['tanggal'] != '') {
    $tanggal = date('d F Y H:i', strtotime($transaksi['tanggal']));
} elseif (isset($transaksi['created_at']) && $transaksi['created_at'] != '0000-00-00 00:00:00' && $transaksi['created_at'] != '') {
    $tanggal = date('d F Y H:i', strtotime($transaksi['created_at']));
} else {
    $tanggal = date('d F Y H:i');
}

// Ambil informasi tambahan dari tabel tiket jika ada
$tiket_info = [];
if (isset($transaksi['tiket_id']) && $transaksi['tiket_id'] > 0) {
    $query_tiket = mysqli_query($conn, "
        SELECT tiket.*, pesawat.nama as pesawat_nama, kelas.nama as kelas_nama 
        FROM tiket 
        LEFT JOIN pesawat ON tiket.pesawat_id = pesawat.id 
        LEFT JOIN kelas ON tiket.kelas_id = kelas.id 
        WHERE tiket.id = '{$transaksi['tiket_id']}'
    ");
    $tiket_info = mysqli_fetch_assoc($query_tiket);
}

// Hitung harga per tiket
$harga_per_tiket = $transaksi['jumlah'] > 0 ? $transaksi['total'] / $transaksi['jumlah'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Transaksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
        }

        .navbar {
            background: #0f172a;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 5px 10px;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .navbar a:hover {
            background: #334155;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 11px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .card-header {
            background: #0f172a;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .card-header h2 {
            margin-bottom: 5px;
        }

        .card-header p {
            opacity: 0.8;
            font-size: 14px;
        }

        .card-body {
            padding: 30px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
        }

        .info-value {
            color: #0f172a;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
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

        .total-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #0f172a;
            font-size: 18px;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #64748b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            transition: background 0.3s;
        }

        .btn-back:hover {
            background: #475569;
        }

        .print-btn {
            background: #22c55e;
            margin-bottom: 10px;
        }

        .print-btn:hover {
            background: #16a34a;
        }

        @media print {
            .navbar, .btn-back, .print-btn {
                display: none;
            }
            .card {
                box-shadow: none;
            }
            .container {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div><b>✈️ DETAIL TRANSAKSI</b></div>
    <div>
        <a href="riwayat.php">← Kembali</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>🧾 Struk Pemesanan</h2>
            <p>ID Transaksi: #<?= $transaksi['id'] ?></p>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Tanggal Pemesanan</span>
                <span class="info-value"><?= $tanggal ?></span>
            </div>
            
            <?php if (!empty($tiket_info)): ?>
                <div class="info-row">
                    <span class="info-label">Pesawat</span>
                    <span class="info-value"><?= htmlspecialchars($tiket_info['pesawat_nama'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kelas</span>
                    <span class="info-value"><?= htmlspecialchars($tiket_info['kelas_nama'] ?? '-') ?></span>
                </div>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">Jumlah Tiket</span>
                <span class="info-value"><?= number_format($transaksi['jumlah']) ?> tiket</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Harga per Tiket</span>
                <span class="info-value">Rp <?= number_format($harga_per_tiket, 0, ',', '.') ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Total Bayar</span>
                <span class="info-value">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></span>
            </div>
            
            <div class="info-row total-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-<?= $transaksi['status'] ?>">
                        <?= ucfirst($transaksi['status']) ?>
                    </span>
                </span>
            </div>
            
            <button onclick="window.print()" class="btn-back print-btn" style="border: none; cursor: pointer;">🖨️ Cetak Struk</button>
            <a href="riwayat.php" class="btn-back">← Kembali ke Riwayat</a>
        </div>
    </div>
</div>

</body>
</html>