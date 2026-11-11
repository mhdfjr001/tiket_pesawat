<?php
session_start();
require "config/koneksi.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } 
    // Validasi no telp (hanya angka dan minimal 10 digit)
    elseif (!preg_match("/^[0-9]{10,13}$/", $no_telp)) {
        $error = "Nomor telepon harus terdiri dari 10-13 digit angka!";
    }
    elseif ($password !== $confirm) {
        $error = "Password tidak cocok!";
    } else {
        // Cek username sudah ada atau belum
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } 
        // Cek email sudah ada atau belum
        else {
            $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, email, no_telp, password, role) VALUES ('$username', '$email', '$no_telp', '$hashed', 'user')";
                if (mysqli_query($conn, $query)) {
                    $success = "Registrasi berhasil! Silakan login.";
                } else {
                    $error = "Registrasi gagal: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
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

    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 90vh;
        padding: 20px;
    }

    .box {
        background: white;
        padding: 30px;
        border-radius: 11px;
        width: 400px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .box h3 {
        margin-bottom: 20px;
        text-align: center;
        color: #0f172a;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-size: 14px;
        font-weight: 500;
    }

    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-sizing: border-box;
        font-size: 14px;
    }

    input:focus {
        outline: none;
        border-color: #0f172a;
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
        margin-top: 10px;
    }

    button:hover {
        opacity: 0.9;
    }

    .error {
        background: #ef4444;
        color: white;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .success {
        background: #22c55e;
        color: white;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .link {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }

    .link a {
        color: #0f172a;
        text-decoration: none;
        font-weight: bold;
    }

    .link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="navbar">
    <div><b>✈️ SISTEM TIKET PESAWAT</b></div>
</div>

<div class="container">
    <div class="box">
        <h3>Register</h3>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='success'>$success</div>"; ?>
        <form method="post">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" placeholder="contoh@email.com" required>
            </div>
            <div class="form-group">
                <label>Nomor Telepon *</label>
                <input type="tel" name="no_telp" placeholder="08123456789" pattern="[0-9]{10,13}" required>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password *</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password" required>
            </div>
            <button type="submit" name="register">Daftar</button>
        </form>
        <div class="link">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>
    </div>
</div>

</body>
</html>