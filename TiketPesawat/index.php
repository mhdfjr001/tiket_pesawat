<?php
session_start();
require "config/koneksi.php";

// cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// data harga tiket
$harga = [
    'GRD' => [
        'nama' => 'Garuda',
        'Eksekutif' => 1500000,
        'Bisnis'    => 900000,
        'Ekonomi'   => 500000
    ],
    'MPT' => [
        'nama' => 'Merpati',
        'Eksekutif' => 1200000,
        'Bisnis'    => 800000,
        'Ekonomi'   => 400000
    ],
    'BTV' => [
        'nama' => 'Batavia',
        'Eksekutif' => 1000000,
        'Bisnis'    => 700000,
        'Ekonomi'   => 300000
    ]
];

$nama = $namaPesawat = $kelas = $kode = '';
$hargaTiket = $jumlah = $totalBayar = $uangBayar = $kembalian = 0;
$message = '';
$showPayment = false;

if (isset($_POST['hitung'])) {
    $nama   = $_POST['nama'] ?? '';
    $kode   = $_POST['kode'] ?? '';
    $kelas  = $_POST['kelas'] ?? '';
    $jumlah = (int)($_POST['jumlah'] ?? 0);

    if (isset($harga[$kode])) {
        $namaPesawat = $harga[$kode]['nama'];
        $hargaTiket  = $harga[$kode][$kelas];
        $totalBayar  = $hargaTiket * $jumlah;
        $showPayment = true;
    }
}

if (isset($_POST['simpan'])) {
    $nama   = $_POST['nama'] ?? '';
    $kode   = $_POST['kode'] ?? '';
    $kelas  = $_POST['kelas'] ?? '';
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $uangBayar = (int)($_POST['uang_bayar'] ?? 0);

    if (isset($harga[$kode])) {
        $namaPesawat = $harga[$kode]['nama'];
        $hargaTiket  = $harga[$kode][$kelas];
        $totalBayar  = $hargaTiket * $jumlah;
        $kembalian = $uangBayar - $totalBayar;
        
        if ($uangBayar >= $totalBayar) {
            // simpan ke database - sesuaikan dengan struktur tabel transaksi
            $user_id = $_SESSION['user_id'];
            $tanggal = date('Y-m-d H:i:s');
            
            // Insert ke tabel transaksi dengan kolom yang sesuai
            $query = "INSERT INTO transaksi (user_id, jumlah, total, status, created_at, tanggal) 
                      VALUES ('$user_id', '$jumlah', '$totalBayar', 'pending', NOW(), NOW())";
            
            if (mysqli_query($conn, $query)) {
                $transaksi_id = mysqli_insert_id($conn);
                $message = "<div style='background:#22c55e; color:white; padding:10px; border-radius:6px; margin-bottom:15px;'>✓ Pemesanan berhasil! ID Transaksi: #$transaksi_id<br>Menunggu konfirmasi admin.</div>";
                // reset form
                $showPayment = false;
                $nama = $namaPesawat = $kelas = $kode = '';
                $hargaTiket = $jumlah = $totalBayar = $uangBayar = $kembalian = 0;
            } else {
                $message = "<div style='background:#ef4444; color:white; padding:10px; border-radius:6px; margin-bottom:15px;'>✗ Gagal menyimpan: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div style='background:#ef4444; color:white; padding:10px; border-radius:6px; margin-bottom:15px;'>✗ Uang bayar kurang Rp " . number_format($totalBayar - $uangBayar, 0, ',', '.') . "</div>";
            $showPayment = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pemesanan Tiket</title>
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .navbar a {
        color: white;
        text-decoration: none;
        margin-left: 15px;
    }

    .navbar a:hover {
        text-decoration: underline;
    }

    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }

    .two-columns {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }

    .col-left {
        flex: 1;
        min-width: 350px;
    }

    .col-right {
        flex: 1;
        min-width: 350px;
    }

    .box {
        background: white;
        padding: 30px;
        border-radius: 11px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .box h3 {
        margin-bottom: 5px;
        text-align: center;
        color: #0f172a;
    }

    .subtitle {
        text-align: center;
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: #0f172a;
        font-size: 14px;
    }

    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    input:focus, select:focus {
        outline: none;
        border-color: #0f172a;
    }

    .radio-group {
        display: flex;
        gap: 20px;
        margin-top: 5px;
        flex-wrap: wrap;
    }

    .radio-group label {
        font-weight: normal;
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }

    .radio-group input {
        width: auto;
        margin: 0;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #0f172a;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: opacity 0.3s;
    }

    button:hover {
        opacity: 0.9;
    }

    .btn-secondary {
        background: #64748b;
        margin-top: 10px;
    }

    .btn-success {
        background: #22c55e;
    }

    .struk-box {
        background: white;
        border-radius: 11px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
        position: sticky;
        top: 20px;
    }

    .struk-header {
        background: #0f172a;
        color: white;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        font-size: 18px;
    }

    .struk-body {
        padding: 20px;
        font-family: 'Courier New', monospace;
        font-size: 14px;
    }

    .struk-title {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px dashed #cbd5e1;
    }

    .struk-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 5px 0;
    }

    .struk-divider {
        border-top: 1px dashed #cbd5e1;
        margin: 12px 0;
    }

    .struk-total {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        padding-top: 10px;
        font-weight: bold;
        font-size: 16px;
        border-top: 2px solid #0f172a;
        color: #0f172a;
    }

    .struk-footer {
        text-align: center;
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px dashed #cbd5e1;
        font-size: 11px;
        color: #94a3b8;
    }

    .empty-struk {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .payment-info {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .payment-info h4 {
        color: #166534;
        margin-bottom: 10px;
    }

    @media (max-width: 800px) {
        .two-columns {
            flex-direction: column;
        }
    }
</style>
<script>
function hitungKembalian() {
    let totalBayar = <?= $totalBayar ?>;
    let uangBayar = document.getElementById('uang_bayar').value;
    let kembalian = uangBayar - totalBayar;
    let kembalianElem = document.getElementById('kembalian_preview');
    
    if (kembalian >= 0) {
        kembalianElem.innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(kembalian);
        kembalianElem.style.color = '#22c55e';
    } else {
        kembalianElem.innerHTML = 'Kurang Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(kembalian));
        kembalianElem.style.color = '#ef4444';
    }
}
</script>
</head>
<body>

<div class="navbar">
    <div><b>✈️ SISTEM TIKET PESAWAT</b></div>
    <div>
        <span>Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="riwayat.php">📋 Riwayat</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="container">
    <div class="two-columns">
        <div class="col-left">
            <div class="box">
                <h3>PEMESANAN TIKET</h3>
                <div class="subtitle">Isi data pemesanan di bawah ini</div>
                
                <?= $message ?>
                
                <form method="post">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Masukkan nama Anda" value="<?= htmlspecialchars($nama) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kode Pesawat</label>
                        <select name="kode" required>
                            <option value="">-- Pilih Pesawat --</option>
                            <option value="GRD" <?= ($kode == 'GRD') ? 'selected' : '' ?>>GRD - Garuda</option>
                            <option value="MPT" <?= ($kode == 'MPT') ? 'selected' : '' ?>>MPT - Merpati</option>
                            <option value="BTV" <?= ($kode == 'BTV') ? 'selected' : '' ?>>BTV - Batavia</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kelas Penerbangan</label>
                        <div class="radio-group">
                            <label><input type="radio" name="kelas" value="Eksekutif" <?= ($kelas == 'Eksekutif') ? 'checked' : '' ?> required> Eksekutif</label>
                            <label><input type="radio" name="kelas" value="Bisnis" <?= ($kelas == 'Bisnis') ? 'checked' : '' ?>> Bisnis</label>
                            <label><input type="radio" name="kelas" value="Ekonomi" <?= ($kelas == 'Ekonomi') ? 'checked' : '' ?>> Ekonomi</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Tiket</label>
                        <input type="number" name="jumlah" min="1" placeholder="Jumlah tiket" value="<?= $jumlah ?: '' ?>" required>
                    </div>

                    <button type="submit" name="hitung">HITUNG TOTAL</button>
                    
                    <?php if ($showPayment): ?>
                        <div class="payment-info">
                            <h4>💳 Detail Pembayaran</h4>
                            <div class="form-group">
                                <label>Total yang harus dibayar</label>
                                <input type="text" value="Rp <?= number_format($totalBayar, 0, ',', '.') ?>" readonly style="background:#f1f5f9; font-weight:bold;">
                            </div>
                            <div class="form-group">
                                <label>Uang yang dibayarkan</label>
                                <input type="number" name="uang_bayar" id="uang_bayar" min="<?= $totalBayar ?>" placeholder="Masukkan jumlah uang" onkeyup="hitungKembalian()" required>
                            </div>
                            <div class="form-group">
                                <label>Kembalian</label>
                                <div id="kembalian_preview" style="font-size: 18px; font-weight: bold; padding: 8px; background:#f8fafc; border-radius:6px;">Rp 0</div>
                            </div>
                        </div>
                        <button type="submit" name="simpan" class="btn-success">✓ BAYAR & SIMPAN</button>
                    <?php endif; ?>
                    
                    <button type="reset" class="btn-secondary" onclick="window.location='<?= $_SERVER['PHP_SELF'] ?>'">RESET</button>
                </form>
            </div>
        </div>

        <div class="col-right">
            <div class="struk-box">
                <div class="struk-header">🧾 STRUK PEMESANAN</div>
                <div class="struk-body">
                    <?php if (isset($_POST['hitung']) && $totalBayar > 0): ?>
                        <div class="struk-title">
                            TIKET PESAWAT<br>
                            <span style="font-size: 12px;">Jakarta - Malaysia</span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-label">Nama</span>
                            <span class="struk-value"><?= htmlspecialchars($nama) ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-label">Pesawat</span>
                            <span class="struk-value"><?= htmlspecialchars($namaPesawat) ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-label">Kelas</span>
                            <span class="struk-value"><?= htmlspecialchars($kelas) ?></span>
                        </div>
                        <div class="struk-divider"></div>
                        <div class="struk-row">
                            <span class="struk-label">Harga Tiket</span>
                            <span class="struk-value">Rp <?= number_format($hargaTiket, 0, ',', '.') ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-label">Jumlah Tiket</span>
                            <span class="struk-value"><?= $jumlah ?> tiket</span>
                        </div>
                        <div class="struk-total">
                            <span>TOTAL BAYAR</span>
                            <span>Rp <?= number_format($totalBayar, 0, ',', '.') ?></span>
                        </div>
                        <?php if (isset($_POST['simpan']) && $uangBayar > 0): ?>
                            <div class="struk-row">
                                <span class="struk-label">Uang Bayar</span>
                                <span class="struk-value">Rp <?= number_format($uangBayar, 0, ',', '.') ?></span>
                            </div>
                            <div class="struk-row">
                                <span class="struk-label">Kembalian</span>
                                <span class="struk-value">Rp <?= number_format($kembalian, 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="struk-footer">
                            Status: <strong>Pending</strong><br>
                            Menunggu konfirmasi admin
                        </div>
                    <?php else: ?>
                        <div class="empty-struk">
                            <div style="font-size: 48px; margin-bottom: 10px;">🧾</div>
                            <p>Belum ada data pemesanan</p>
                            <p style="font-size: 12px; margin-top: 8px;">Silakan isi form di samping<br>dan klik HITUNG TOTAL</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>